<?php

/**
 * Validation and generation of EGN (personal identification numbers for Bulgarian citizens)
 * Ivan Tcholakov <ivantcholakov@gmail.com>, May, 2013.
 * This piece of code is basen upon the following origin: http://georgi.unixsol.org/programs/egn.php
 * The license stays the same.
 * Code repository: https://github.com/ivantcholakov/egn
 *
 * Валидация и генериране на ЕГН
 * Иван Чолаков <ivantcholakov@gmail.com>, май 2013.
 * Кодът е базиран на следния първоизточник: http://georgi.unixsol.org/programs/egn.php
 * Лицензът остава същият.
 * Хранилище на кода: https://github.com/ivantcholakov/egn
 */

# Информация, проверка и генератор за единни граждански номера (ЕГН)
# Версия 1.50 (30-Sep-2006)
#
# За контакти:
#   E-mail: georgi@unixsol.org
#   WWW   : http://georgi.unixsol.org/
#   Source: http://georgi.unixsol.org/programs/egn.php
#
# Copyright (c) 2006 Georgi Chorbadzhiyski
# All rights reserved.
#
# Redistribution and use of this script, with or without modification, is
# permitted provided that the following conditions are met:
#
# 1. Redistributions of this script must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
#
#  THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
#  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
#  MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO
#  EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
#  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
#  PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
#  OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
#  WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
#  OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
#  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#

class Egn {

    protected static $EGN_WEIGHTS = array(2, 4, 8, 5, 10, 9, 7, 3, 6);

    protected static $EGN_REGIONS = array(
        'Благоевград' =>        43,  /* от 000 до 043 */
        'Бургас' =>             93,  /* от 044 до 093 */
        'Варна' =>              139, /* от 094 до 139 */
        'Велико Търново' =>     169, /* от 140 до 169 */
        'Видин' =>              183, /* от 170 до 183 */
        'Враца' =>              217, /* от 184 до 217 */
        'Габрово' =>            233, /* от 218 до 233 */
        'Кърджали' =>           281, /* от 234 до 281 */
        'Кюстендил' =>          301, /* от 282 до 301 */
        'Ловеч' =>              319, /* от 302 до 319 */
        'Монтана' =>            341, /* от 320 до 341 */
        'Пазарджик' =>          377, /* от 342 до 377 */
        'Перник' =>             395, /* от 378 до 395 */
        'Плевен' =>             435, /* от 396 до 435 */
        'Пловдив' =>            501, /* от 436 до 501 */
        'Разград' =>            527, /* от 502 до 527 */
        'Русе' =>               555, /* от 528 до 555 */
        'Силистра' =>           575, /* от 556 до 575 */
        'Сливен' =>             601, /* от 576 до 601 */
        'Смолян' =>             623, /* от 602 до 623 */
        'София - град' =>       721, /* от 624 до 721 */
        'София - окръг' =>      751, /* от 722 до 751 */
        'Стара Загора' =>       789, /* от 752 до 789 */
        'Добрич (Толбухин)' =>  821, /* от 790 до 821 */
        'Търговище' =>          843, /* от 822 до 843 */
        'Хасково' =>            871, /* от 844 до 871 */
        'Шумен' =>              903, /* от 872 до 903 */
        'Ямбол' =>              925, /* от 904 до 925 */
        'Друг/Неизвестен' =>    999, /* от 926 до 999 - Такъв регион понякога се ползва при
                                                        родени преди 1900, за родени в чужбина
                                                        или ако в даден регион се родят повече
                                                        деца от предвиденото. Доколкото ми е
                                                        известно няма правило при ползването
                                                        на 926 - 999 */
    );

    protected static $EGN_REGIONS_LAST_NUM  = array();
    protected static $EGN_REGIONS_FIRST_NUM = array();

    protected static $MONTHS_BG = array(
         1 => 'януари',
         2 => 'февруари',
         3 => 'март',
         4 => 'април',
         5 => 'май',
         6 => 'юни',
         7 => 'юли',
         8 => 'август',
         9 => 'септември',
        10 => 'октомври',
        11 => 'ноември',
        12 => 'декември'
    );

    protected static function initialize() {

        $first_region_num = 0;

        foreach (self::$EGN_REGIONS as $region => $last_region_num) {
            self::$EGN_REGIONS_FIRST_NUM[$first_region_num] = $last_region_num;
            self::$EGN_REGIONS_LAST_NUM[$last_region_num] = $first_region_num;
            $first_region_num = $last_region_num + 1;
        }
    }

    protected static function EGN_REGIONS_LAST_NUM() {

        if (empty(self::$EGN_REGIONS_LAST_NUM)) {
            self::initialize();
        }

        return self::$EGN_REGIONS_LAST_NUM;
    }

    protected static function EGN_REGIONS_FIRST_NUM() {

        if (empty(self::$EGN_REGIONS_FIRST_NUM)) {
            self::initialize();
        }

        return self::$EGN_REGIONS_FIRST_NUM;
    }

    /* Check if EGN is valid */
    /* See: http://www.grao.bg/esgraon.html */
    public static function valid($egn) {

        $egn = (string) $egn;

        // Added by Ivan Tcholakov, 25-JUN-2017.
        if (!ctype_digit($egn)) {
            return false;
        }
        //

        if (strlen($egn) != 10) {
            return false;
        }

        $year = substr($egn, 0, 2);
        $mon  = substr($egn, 2, 2);
        $day  = substr($egn, 4, 2);

        if ($mon > 40) {

            if (!checkdate($mon - 40, $day, $year + 2000)) return false;

        } elseif ($mon > 20) {

            if (!checkdate($mon - 20, $day, $year + 1800)) return false;

        } else {

            if (!checkdate($mon, $day, $year + 1900)) return false;
        }

        $checksum = substr($egn, 9, 1);
        $egnsum = 0;

        for ($i = 0; $i < 9; $i++) {
            $egnsum += substr($egn, $i, 1) * self::$EGN_WEIGHTS[$i];
        }

        $valid_checksum = $egnsum % 11;

        if ($valid_checksum == 10) {
            $valid_checksum = 0;
        }

        if ($checksum == $valid_checksum) {
            return true;
        }

        return false;
    }

    /* Return array with EGN info */
    public static function parse($egn) {

        $egn = (string) $egn;

        if (!self::valid($egn)) {
            return false;
        }

        $ret = array();
        $ret['year']  = substr($egn, 0, 2);
        $ret['month'] = substr($egn, 2, 2);
        $ret['day']   = substr($egn, 4, 2);

        if ($ret['month'] > 40) {
            $ret['month'] -= 40;
            $ret['year']  += 2000;
        }
        elseif ($ret['month'] > 20) {
            $ret['month'] -= 20;
            $ret['year']  += 1800;
        } else {
            $ret['year']  += 1900;
        }

        $ret['birthday_text'] = (int)$ret['day'].' '.self::$MONTHS_BG[(int)$ret['month']].' '.$ret['year'].' г.';
        // Added by Ivan Tcholakov, 24-AUG-2013.
        $ret['birthday'] = sprintf('%04d', (int) $ret['year']).'-'.sprintf('%02d', (int) $ret['month']).'-'.sprintf('%02d', (int) $ret['day']);
        //
        $region = substr($egn, 6, 3);
        $ret['region_num'] = $region;
        $ret['sex'] = substr($egn, 8, 1) % 2;
        $ret['sex_text'] = 'жена';
        if (!$ret['sex']) {
            $ret['sex_text'] = 'мъж';
        }
        // Added by Ivan Tcholakov, 24-AUG-2013.
        $ret['gender'] = (bool) $ret['sex'] ? 'f' : 'm';
        //

        $first_region_num = 0;
        foreach (self::$EGN_REGIONS as $region_name => $last_region_num) {
            if ($region >= $first_region_num && $region <= $last_region_num) {
                $ret['region_text'] = $region_name;
                break;
            }
            $first_region_num = $last_region_num+1;
        }
        if (substr($egn,8,1) % 2 != 0) {
            $region--;
        }
        $ret['birthnumber'] = ($region - $first_region_num) / 2 + 1;

        return $ret;
    }

    /* Return text with EGN info */
    public static function info($egn) {

        if (!self::valid($egn)) {
            return "<strong>" . htmlspecialchars($egn) ."</strong> невалиден ЕГН";
        }

        $data = self::parse($egn);

        $ret  = "<strong>".htmlspecialchars($egn)."</strong> е ЕГН на <strong>{$data['sex_text']}</strong>, ";
        $ret .= "роден".($data["sex"]?"а":"")." на <strong>{$data['birthday_text']}</strong> в ";
        $ret .= "регион <strong>{$data['region_text']}</strong> ";
        if ($data["birthnumber"]-1) {
            $ret .= "като преди ".($data["sex"]?"нея":"него")." ";
            if ($data["birthnumber"]-1 > 1) {
                $ret .= "в този ден и регион са се родили <strong>".($data["birthnumber"]-1)."</strong>";
                $ret .= $data["sex"]?" момичета":" момчета";
            } else {
                $ret .= "в този ден и регион се е родило <strong>1</strong>";
                $ret .= $data["sex"]?" момиче":" момче";
            }
        } else {
            $ret .= "като е ".($data["sex"]?"била":"бил")." ";
            $ret .= "<strong>първото ".($data["sex"]?" момиче":" момче")."</strong> ";
            $ret .= "родено в този ден и регион";
        }

        return $ret;
    }

    /* Generate EGN. When parameter is 0 || false it is randomized */
    public static function generate($day = 0, $mon = 0, $year = 0, $sex = 0, $region = false) {

        $EGN_REGIONS_FIRST_NUM = self::EGN_REGIONS_FIRST_NUM();

        $day = $day  > 0 ? min($day, 31) : ($day < 0 ? 0 : $day);
        $mon = $mon  > 0 ? min($mon, 12) : ($mon < 0 ? 0 : $mon);
        $year= $year > 1799 ? min($year, 2099) : ($year == 0 ? $year : 1800);
        $region = isset($EGN_REGIONS_FIRST_NUM[$region]) ? $region : false;

        $iter = 0;
        do {
            $gday  = $day  ? $day  : rand(1, 31);
            $gmon  = $mon  ? $mon  : rand(1, 12);
            $gyear = $year ? $year : rand(1900, 2010);
            $iter++;
        } while (!checkdate($gmon, $gday, $gyear) && $iter < 3);
        $cent = $gyear - ($gyear % 100);
        if ($iter > 3) {
            return false;
        }

        /* Fixes for other centuries */
        switch ($cent) {
            case 1800: $gmon += 20; break;
            case 2000: $gmon += 40; break;
        }

        /* Generate region/sex */
        if ($region === false) {
            $gregion = rand(0, 999);
        } else {
            $gregion = rand($region, $EGN_REGIONS_FIRST_NUM[$region]);
        }

        /* Make it odd */
        if ($sex == 1 && ($gregion % 2 != 0)) {
            $gregion--;
        }

        /* Make it even */
        if ($sex == 2 && ($gregion % 2 == 0)) {
            $gregion++;
        }

        /* Create EGN */
        $egn = str_pad($gyear - $cent, 2, '0', STR_PAD_LEFT) .
               str_pad($gmon, 2, '0', STR_PAD_LEFT) .
               str_pad($gday, 2, '0', STR_PAD_LEFT) .
               str_pad($gregion, 3, '0', STR_PAD_LEFT);

        /* Calculate checksum */
        $egnsum = 0;
        for ($i = 0; $i < 9; $i++) {
            $egnsum += substr($egn, $i, 1) * self::$EGN_WEIGHTS[$i];
        }
        $valid_checksum = $egnsum % 11;
        if ($valid_checksum == 10) {
            $valid_checksum = 0;
        }
        $egn .= $valid_checksum;

        return $egn;
    }

    // Additional methods by Ivan Tcholakov.

    public static function get_gender($string) {

        if (self::is_foreigner($string)) {
            return null;
        }

        if (!self::valid($string)) {
            return null;
        }

        $info = self::parse($string);

        return $info['gender'];
    }

    public static function get_birthday($string) {

        if (!self::valid($string)) {
            return null;
        }

        $info = self::parse($string);

        return $info['birthday'];
    }

    /* Is it a personal number of foreigner? A simple check. */
    public static function is_foreigner($string) {

        $string = (string) $string;

        if (!ctype_digit($string)) {
            return false;
        }

        if (self::valid($string)) {
            return false;
        }

        if (strlen($string) == 10) {

            $pnf_weights = array(21, 19, 17, 13, 11, 9, 7, 3, 1);

            $checksum = substr($string, 9, 1);
            $pnfsum = 0;

            for ($i = 0; $i < 9; $i++) {
                $pnfsum += substr($string, $i, 1) * $pnf_weights[$i];
            }

            $valid_checksum = $pnfsum % 10;

            if ($checksum == $valid_checksum) {
                return true;
            }

            return false;
        }

        if (strlen($string) == 11) {

            if (substr($string, 0, 3) == '229') {
                return true;
            }

            return false;
        }

        return false;
    }

}
