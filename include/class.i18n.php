<?php
/*********************************************************************
    class.i18n.php
    
    Internationalization using the Gettext library.
    
    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

class i18n {

    public $name;
    public $description;

    public function __construct($name, $description) {
        $this->name = $name;
        $this->description = $description;
    }

    static function getLanguages() {

       $langs[] = new i18n("en", "English (default)");
       $langs[] = new i18n("de_DE", "Deutsch");
       $langs[] = new i18n("es_ES", "Espa&ntilde;ol");
       $langs[] = new i18n("it_IT", "Italiano");
       $langs[] = new i18n("nl_NL", "Nederlands");
       $langs[] = new i18n("pt_BR", "Portugu&ecirc;s do Brasil");
       $langs[] = new i18n("ru_RU", "Русский");
       
       return $langs;
    }

}