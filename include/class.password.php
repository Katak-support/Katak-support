<?php
/*********************************************************************
    class.password.php

    Secure password hashing functions based on the Portable PHP password
    hashing framework. See http://www.openwall.com/phpass/

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
class PhpassHashedPass {

  // The minimum allowed log2 number of iterations for password stretching.
  const MIN_HASH_COUNT = 7;

  // The maximum allowed log2 number of iterations for password stretching.
  const MAX_HASH_COUNT = 30;

  // The number of iteration in the hash process.
  // Must be between MIN_HASH_COUNT and MAX_HASH_COUNT.
  const HASH_ITERATION = 8;
  
  // The expected (and maximum) number of characters in a hashed password.
  const HASH_LENGTH = 55;

  // Returns a string for mapping an int to the corresponding base 64 character.
  static $ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';


  // Constructs a new phpass password hashing instance.
  function __construct() {
  }

  /**
   * Encodes bytes into printable base 64 using the *nix standard from crypt().
   *
   * @param String $input
   *   The string containing bytes to encode.
   * @param Integer $count
   *   The number of characters (bytes) to encode.
   *
   * @return String
   *   Encoded string
   */
  protected static function base64Encode($input, $count) {
    $output = '';
    $i = 0;
    do {
      $value = ord($input[$i++]);
      $output .= self::$ITOA64[$value & 0x3f];
      if ($i < $count) {
        $value |= ord($input[$i]) << 8;
      }
      $output .= self::$ITOA64[($value >> 6) & 0x3f];
      if ($i++ >= $count) {
        break;
      }
      if ($i < $count) {
        $value |= ord($input[$i]) << 16;
      }
      $output .= self::$ITOA64[($value >> 12) & 0x3f];
      if ($i++ >= $count) {
        break;
      }
      $output .= self::$ITOA64[($value >> 18) & 0x3f];
    } while ($i < $count);

    return $output;
  }
  
  
  protected static function get_random_bytes($count) {
    $output = '';
    if (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
      $output = fread($fh, $count);
      fclose($fh);
    }

    if (strlen($output) < $count) {
      $output = '';
      for ($i = 0; $i < $count; $i += 16) {
        $random_state =
            md5(microtime() . $random_state);
        $output .=
            pack('H*', md5($random_state));
      }
      $output = substr($output, 0, $count);
    }

    return $output;
  }

  /**
   * Generates a random base 64-encoded salt prefixed with settings for the hash.
   *
   * Proper use of salts may defeat a number of attacks, including:
   *  - The ability to try candidate passwords against multiple hashes at once.
   *  - The ability to use pre-hashed lists of candidate passwords.
   *  - The ability to determine whether two users have the same (or different)
   *    password without actually having to guess one of the passwords.
   *
   * @return String
   *   A 12 character string containing the iteration count and a random salt.
   */
  protected static function generateSalt() {
    $output = '$S$';
    // We encode the final log2 iteration count in base 64.
    $output .= self::$ITOA64[self::HASH_ITERATION];
    // 6 bytes is the standard salt for a portable phpass hash.
    $output .= self::base64Encode(self::get_random_bytes(6), 6);
    return $output;
  }

  /**
   * Hash a password using a secure stretched hash.
   *
   * By using a salt and repeated hashing the password is "stretched". Its
   * security is increased because it becomes much more computationally costly
   * for an attacker to try to break the hash by brute-force computation of the
   * hashes of a large number of plain-text words or strings to find a match.
   *
   * @param String $algo
   *   The string name of a hashing algorithm usable by hash(), like 'sha256'.
   * @param String $password
   *   The plain-text password to hash.
   * @param String $setting
   *   An existing hash or the output of self::generateSalt().  Must be
   *   at least 12 characters (the settings and salt).
   *
   * @return String
   *   A string containing the hashed password (and salt) or FALSE on failure.
   *   The return string will be truncated at HASH_LENGTH characters max.
   */
  protected static function crypt($algo, $password, $setting) {
    // The first 12 characters of an existing hash are its setting string.
    $setting = substr($setting, 0, 12);

    // Bad password!
    if ($setting[0] != '$' || $setting[2] != '$') {
      return FALSE;
    }
    $count_log2 = self::getCountLog2($setting);
    // Stored hashes may have been crypted with any iteration count. However we
    // do not allow applying the algorithm for unreasonable low and heigh
    // values respectively.
    if (($count_log2 < self::MIN_HASH_COUNT) || ($count_log2 > self::MAX_HASH_COUNT))
      return  FALSE;

    $salt = substr($setting, 4, 8);
    // Hashes must have an 8 character salt.
    if (strlen($salt) != 8) {
      return FALSE;
    }

    // Convert the base 2 logarithm into an integer.
    $count = 1 << $count_log2;

    // We rely on the hash() function being available in PHP 5.2+.
    $hash = hash($algo, $salt . $password, TRUE);
    do {
      $hash = hash($algo, $hash . $password, TRUE);
    } while (--$count);

    $len = strlen($hash);
    $output =  $setting . self::base64Encode($hash, $len);
    // self::base64Encode() of a 16 byte MD5 will always be 22 characters.
    // self::base64Encode() of a 64 byte sha512 will always be 86 characters.
    $expected = 12 + ceil((8 * $len) / 6);
    return (strlen($output) == $expected) ? substr($output, 0, self::HASH_LENGTH) : FALSE;
  }

  /**
   * Parse the log2 iteration count from a stored hash or setting string.
   *
   * @param String $setting
   *   An existing hash or the output of self::generateSalt().  Must be
   *   at least 12 characters (the settings and salt).
   */
  protected static function getCountLog2($setting) {
    return strpos(self::$ITOA64, $setting[3]);
  }

  // Generate the hash of the password
  // We use a static function to avoid the class instantiation
  static function hash($password) {
    return self::crypt('sha512', $password, self::generateSalt());
  }

  // Check password
  static function check($password, $account) {
    $type = substr($account, 0, 3);
    switch ($type) {
      case '$S$':
        // A normal password using sha512.
        $hash = self::crypt('sha512', $password, $account);
        break;
      case '$H$':
        // phpBB3 uses "$H$" for the same thing as "$P$".
      case '$P$':
        // A phpass password generated using md5.  This is an
        // imported password or from an earlier version.
        $hash = self::crypt('md5', $password, $account);
        break;
      default:
        // We suppose it is an old version with simple md5 hash.
        $hash = MD5($password);
    }
    return ($hash && $account == $hash);
  }

}
?>