<?php

class Dropdowns extends MyDropdowns
{

    // contains arrays of US states, Canadian provinces/territories, countries of the world and many more - to be used in dropdown menus
    // you can easily add your own and then call them by using the input_select() function or in the fastform() function

    // months with full name as key
    public static function months()
    {
        return [
            'January' => 'January',
            'February' => 'February',
            'March' => 'March',
            'April' => 'April',
            'May' => 'May',
            'June' => 'June',
            'July' => 'July',
            'August' => 'August',
            'September' => 'September',
            'October' => 'October',
            'November' => 'November',
            'December' => 'December'
        ];
    }

    public static function days()
    {
        $stop_day = 31;

        // get the current year
        $start_day = 1;

        // initialize the years array
        $days = [];

        // starting with the current year, 
        // loop through the years until we reach the stop date
        for ($i = $start_day; $i <= $stop_day; $i++) {
            $days[$i] = $i;
        }

        return $days;
    }

    # displays every year starting from 1950, good for registration forms
    public static function years()
    {
        $stop_date = date('Y');

        // get the current year
        $start_date = 1950;

        // initialize the years array
        $years = [];

        // starting with the current year, 
        // loop through the years until we reach the stop date
        for ($i = $start_date; $i <= $stop_date; $i++) {
            $years[$i] = $i;
        }

        // reverse the array so we have 1900 at the bottom of the menu
        $return = array_reverse($years, true);

        return $return;
    }

    // displays months of the year
    public static function months_alpha()
    {
        return [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
    }

    // months - good for credit cards
    public static function cc_months()
    {
        return [
            1  => '01 - January',
            2  => '02 - February',
            3  => '03 - March',
            4  => '04 - April',
            5  => '05 - May',
            6  => '06 - June',
            7  => '07 - July',
            8  => '08 - August',
            9  => '09 - September',
            10 => '10 - October',
            11 => '11 - November',
            12 => '12 - December'
        ];
    }


    // years - for credit cards
    public static function cc_years()
    {
        $stop_date = 2025;

        // get the current year
        $current_year = date('Y');

        // initialize the years array
        $years = [];

        // starting with the current year, 
        // loop through the years until we reach the stop date
        for ($i = $current_year; $i <= $stop_date; $i++) {
            $years[$i] = $i;
        }

        return $years;
    }


    public static function height()
    {
        return [
            '3-0' => "Under 4'",
            '4-0' => "4' 0&quot",
            '4-1' => "4' 1&quot",
            '4-2' => "4' 2&quot",
            '4-3' => "4' 3&quot",
            '4-4' => "4' 4&quot",
            '4-5' => "4' 5&quot",
            '4-6' => "4' 6&quot",
            '4-7' => "4' 7&quot",
            '4-8' => "4' 8&quot",
            '4-9' => "4' 9&quot",
            '4-10' => "4' 10&quot",
            '4-11' => "4' 11&quot",
            '5-0' => "5' 0&quot",
            '5-1' => "5' 1&quot",
            '5-2' => "5' 2&quot",
            '5-3' => "5' 3&quot",
            '5-4' => "5' 4&quot",
            '5-5' => "5' 5&quot",
            '5-6' => "5' 6&quot",
            '5-7' => "5' 7&quot",
            '5-8' => "5' 8&quot",
            '5-9' => "5' 9&quot",
            '5-10' => "5' 10&quot",
            '5-11' => "5' 11&quot",
            '6-0' => "6' &amp; Over",
        ];
    }


    // makes sure a person is of a certain age - in this cas: 18
    public static function years_old()
    {
        $stop_date = date('Y', strtotime('-18 year'));

        $start_date = 1950;

        // initialize the years array
        $years = [];

        // starting with the current year, 
        // loop through the years until we reach the stop date
        for ($i = $start_date; $i <= $stop_date; $i++) {
            $years[$i] = $i;
        }

        // reverse the array so we have the start date at the bottom of the menu
        $return = array_reverse($years, true);

        return $return;
    }


    public static function age()
    {
        foreach (range(18, 24) as $value) {
            $ages[$value] = $value;
        }
        $ages[25] = '25-29';
        $ages[30] = '30-34';
        $ages[35] = '35-39';
        $ages[40] = '40-44';
        $ages[45] = '45-49';
        $ages[50] = '50+';

        return $ages;
    }


    // US states
    public static function states()
    {
        return [
            '' => 'Select a State...',
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'DC' => 'Washington D.C.',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming'
        ];
    }

    // alias of states()
    public static function state()
    {
        return static::states();
    }


    // Canadian provinces and territories
    public static function provinces()
    {
        return [
            '' => 'Province or Territory...',
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NS' => 'Nova Scotia',
            'NT' => 'Northwest Territories',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon'
        ];
    }


    public static function states_provinces()
    {
        return [
            '' => 'Select a State or Province...',
            'States' => [
                'AL' => 'Alabama',
                'AK' => 'Alaska',
                'AZ' => 'Arizona',
                'AR' => 'Arkansas',
                'CA' => 'California',
                'CO' => 'Colorado',
                'CT' => 'Connecticut',
                'DE' => 'Delaware',
                'FL' => 'Florida',
                'GA' => 'Georgia',
                'HI' => 'Hawaii',
                'ID' => 'Idaho',
                'IL' => 'Illinois',
                'IN' => 'Indiana',
                'IA' => 'Iowa',
                'KS' => 'Kansas',
                'KY' => 'Kentucky',
                'LA' => 'Louisiana',
                'ME' => 'Maine',
                'MD' => 'Maryland',
                'MA' => 'Massachusetts',
                'MI' => 'Michigan',
                'MN' => 'Minnesota',
                'MS' => 'Mississippi',
                'MO' => 'Missouri',
                'MT' => 'Montana',
                'NE' => 'Nebraska',
                'NV' => 'Nevada',
                'NH' => 'New Hampshire',
                'NJ' => 'New Jersey',
                'NM' => 'New Mexico',
                'NY' => 'New York',
                'NC' => 'North Carolina',
                'ND' => 'North Dakota',
                'OH' => 'Ohio',
                'OK' => 'Oklahoma',
                'OR' => 'Oregon',
                'PA' => 'Pennsylvania',
                'RI' => 'Rhode Island',
                'SC' => 'South Carolina',
                'SD' => 'South Dakota',
                'TN' => 'Tennessee',
                'TX' => 'Texas',
                'UT' => 'Utah',
                'VT' => 'Vermont',
                'VA' => 'Virginia',
                'WA' => 'Washington',
                'DC' => 'Washington D.C.',
                'WV' => 'West Virginia',
                'WI' => 'Wisconsin',
                'WY' => 'Wyoming'
        ],
            'Provinces' => [
                'AB' => 'Alberta',
                'BC' => 'British Columbia',
                'MB' => 'Manitoba',
                'NB' => 'New Brunswick',
                'NL' => 'Newfoundland and Labrador',
                'NS' => 'Nova Scotia',
                'NT' => 'Northwest Territories',
                'NU' => 'Nunavut',
                'ON' => 'Ontario',
                'PE' => 'Prince Edward Island',
                'QC' => 'Quebec',
                'SK' => 'Saskatchewan',
                'YT' => 'Yukon'
            ]
        ];
    }


    // Countries
    public static function countries()
    {
        return [
            '' => 'Select a Country...',
            'US' => 'United States ',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            ' ' => '---------------',
            'AF' => 'Afghanistan ',
            'AL' => 'Albania ',
            'DZ' => 'Algeria ',
            'AS' => 'American Samoa',
            'AD' => 'Andorra ',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda ',
            'AR' => 'Argentina ',
            'AM' => 'Armenia ',
            'AW' => 'Aruba ',
            'AU' => 'Australia ',
            'AT' => 'Austria ',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas ',
            'BH' => 'Bahrain ',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus ',
            'BE' => 'Belgium ',
            'BZ' => 'Belize',
            'BJ' => 'Benin ',
            'BM' => 'Bermuda ',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia ',
            'BA' => 'Bosnia and Herzegowina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island ',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam ',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi ',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile ',
            'CN' => 'China ',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands ',
            'CO' => 'Colombia',
            'KM' => 'Comoros ',
            'CG' => 'Congo ',
            'CD' => 'Congo, the Democratic Republic of the ',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Cote d\'Ivoire ',
            'HR' => 'Croatia (Hrvatska)',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark ',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'TP' => 'East Timor',
            'EC' => 'Ecuador ',
            'EG' => 'Egypt ',
            'SV' => 'El Salvador ',
            'GQ' => 'Equatorial Guinea ',
            'ER' => 'Eritrea ',
            'EE' => 'Estonia ',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas) ',
            'FO' => 'Faroe Islands ',
            'FJ' => 'Fiji',
            'FI' => 'Finland ',
            'FR' => 'France',
            'FX' => 'France, Metropolitan',
            'GF' => 'French Guiana ',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories ',
            'GA' => 'Gabon ',
            'GM' => 'Gambia',
            'GE' => 'Georgia ',
            'DE' => 'Germany ',
            'GH' => 'Ghana ',
            'GI' => 'Gibraltar ',
            'GR' => 'Greece',
            'GL' => 'Greenland ',
            'GD' => 'Grenada ',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala ',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau ',
            'GY' => 'Guyana',
            'HT' => 'Haiti ',
            'HM' => 'Heard and Mc Donald Islands ',
            'VA' => 'Holy See (Vatican City State) ',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong ',
            'HU' => 'Hungary ',
            'IS' => 'Iceland ',
            'IN' => 'India ',
            'ID' => 'Indonesia ',
            'IR' => 'Iran (Islamic Republic of)',
            'IQ' => 'Iraq',
            'IE' => 'Ireland ',
            'IL' => 'Israel',
            'IT' => 'Italy ',
            'JM' => 'Jamaica ',
            'JP' => 'Japan ',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya ',
            'KI' => 'Kiribati',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon ',
            'LS' => 'Lesotho ',
            'LR' => 'Liberia ',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein ',
            'LT' => 'Lithuania ',
            'LU' => 'Luxembourg',
            'MO' => 'Macau ',
            'MK' => 'Macedonia, The Former Yugoslav Republic of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta ',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius ',
            'YT' => 'Mayotte ',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States of ',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'MS' => 'Montserrat',
            'MA' => 'Morocco ',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar ',
            'NA' => 'Namibia ',
            'NR' => 'Nauru ',
            'NP' => 'Nepal ',
            'NL' => 'Netherlands ',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia ',
            'NZ' => 'New Zealand ',
            'NI' => 'Nicaragua ',
            'NE' => 'Niger ',
            'NG' => 'Nigeria ',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau ',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines ',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico ',
            'QA' => 'Qatar ',
            'RE' => 'Reunion ',
            'RO' => 'Romania ',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'KN' => 'Saint Kitts and Nevis ',
            'LC' => 'Saint LUCIA ',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa ',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe ',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal ',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore ',
            'SK' => 'Slovakia (Slovak Republic)',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands ',
            'SO' => 'Somalia ',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain ',
            'LK' => 'Sri Lanka ',
            'SH' => 'St. Helena',
            'PM' => 'St. Pierre and Miquelon ',
            'SD' => 'Sudan ',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen Islands',
            'SZ' => 'Swaziland ',
            'SE' => 'Sweden',
            'CH' => 'Switzerland ',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand',
            'TG' => 'Togo',
            'TK' => 'Tokelau ',
            'TO' => 'Tonga ',
            'TT' => 'Trinidad and Tobago ',
            'TN' => 'Tunisia ',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine ',
            'AE' => 'United Arab Emirates',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay ',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu ',
            'VE' => 'Venezuela ',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands (British)',
            'VI' => 'Virgin Islands (U.S.) ',
            'WF' => 'Wallis and Futuna Islands ',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen ',
            'YU' => 'Yugoslavia',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe'
        ];
    }

    // alias of countries()
    public static function country()
    {
        return static::countries();
    }
}
