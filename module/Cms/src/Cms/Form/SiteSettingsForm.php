<?php
// module/Cms/src/Cms/Form/CreateUserForm.php:
namespace Cms\Form;

use Zend\Form\Form;

class SiteSettingsForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('enctype','multipart/form-data');
		$this->setAttribute('class', 'form-horizontal');
		$this->setAttribute('name', 'siteSettings');
		$this->setAttribute('id', 'siteSettings');
		
		$this->add(array(
            'name' => 'carrier_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
		$this->add(array(
            'name' => 'hidden_carrier_logo',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
		$this->add(array(
            'name' => 'hidden_carrier_banner',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		
		$this->add(array(
            'name' => 'hidden_carrier_foryou',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		$this->add(array(
            'name' => 'hidden_carrier_forafriend',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		$this->add(array(
            'name' => 'hidden_carrier_ask',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_fbappid',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'carrier_fbappid',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_fbkey',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'carrier_fbkey',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_fbapp_name',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'carrier_fbapp_name',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_fb_page',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'carrier_fb_page',
				'class'	=> 'input-large',
				'maxlength'	=> '255',
				'style' => 'width:500px',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'carrier_font',
            'options' => array(
                'value_options' => array(
                    '' 				=> 'Select Font',
                    "Arial"			=> "Arial", 
					"Arial Black"	=> "Arial Black",
					"Comic Sans Ms"	=> "Comic Sans Ms",
					"Courier"		=> "Courier",
					"Courier New"	=> "Courier New",
					"Cursive"		=> "Cursive",
					"Fantasy"		=> "Fantasy",
					"Georgia"		=> "Georgia",
					"Helvetica"		=> "Helvetica",
					"Monospace"		=> "Monospace",
					"Proxima Nova"	=> "Proxima Nova",
					"Sans-Serif"	=> "Sans-Serif",
					"Tahoma"		=> "Tahoma",
					"Times New Roman"	=> "Times New Roman",
					"Trebuchet Ms"	=> "Trebuchet Ms",
					"Verdana"		=> "Verdana"
                ),
            ),
            'attributes' => array(
                'value' => '',
				'id'	=> 'carrier_font'
            )
        ));
		
		$this->add(array(
            'name' => 'carrier_themecolor',
            'attributes'	=> array(
                'type' 		=> 'text',
				'id'		=> 'colorpickerField1',
				'onblur'	=> 'changePickedColor(this.value, 1);',
				'onkeyup'	=> 'changePickedColor(this.value, 1);',
				'onfocus'	=> 'setField(1);',
				'class'		=> 'input-large',
				'maxlength'	=> '6',
				'size'		=> '6',
				'value'		=> 'FFFFFF',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_backgroundcolor',
            'attributes'	=> array(
                'type' 		=> 'text',
				'id'		=> 'colorpickerField2',
				'onblur'	=> 'changePickedColor(this.value, 2);',
				'onkeyup'	=> 'changePickedColor(this.value, 2);',
				'onfocus'	=> 'setField(2);',
				'class'		=> 'input-large',
				'maxlength'	=> '6',
				'size'		=> '6',
				'value'		=> 'FFFFFF',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_buttoncolor',
            'attributes'	=> array(
                'type' 		=> 'text',
				'id'		=> 'colorpickerField3',
				'onblur'	=> 'changePickedColor(this.value, 3);',
				'onkeyup'	=> 'changePickedColor(this.value, 3);',
				'onfocus'	=> 'setField(3);',
				'class'		=> 'input-large',
				'maxlength'	=> '6',
				'size'		=> '6',
				'value'		=> 'FFFFFF',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_buttonhighlightcolor',
            'attributes'	=> array(
                'type' 		=> 'text',
				'id'		=> 'colorpickerField5',
				'onblur'	=> 'changePickedColor(this.value, 5);',
				'onkeyup'	=> 'changePickedColor(this.value, 5);',
				'onfocus'	=> 'setField(5);',
				'class'		=> 'input-large',
				'maxlength'	=> '6',
				'size'		=> '6',
				'value'		=> '148E51',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'name' => 'carrier_fontcolor',
            'attributes'	=> array(
                'type' 		=> 'text',
				'id'		=> 'colorpickerField4',
				'onblur'	=> 'changePickedColor(this.value, 4);',
				'onkeyup'	=> 'changePickedColor(this.value, 4);',
				'onfocus'	=> 'setField(4);',
				'class'		=> 'input-large',
				'maxlength'	=> '6',
				'size'		=> '6',
				'value'		=> 'FFFFFF',
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
			'name'	=> 'carrier_logo',
			'attributes' => array(
				'id' => 'carrier_logo',
				'type'  => 'file',
				'class' => 'input-file uniform_on',
				'style' => 'opacity: 0;',
				'onchange' => 'readURL(this);',
			),
			'options' => array(
            ),
		));
		
		$this->add(array(
			'name'	=> 'carrier_banner',
			'attributes' => array(
				'id' => 'carrier_banner',
				'type'  => 'file',
				'class' => 'input-file uniform_on',
				'style' => 'opacity: 0;',
				'onchange' => 'readBannerURL(this);',
			),
			'options' => array(
            ),
		));
		
		$this->add(array(
			'name'	=> 'carrier_foryou_logo',
			'attributes' => array(
				'id' => 'carrier_foryou_logo',
				'type'  => 'file',
				'class' => 'input-file uniform_on',
				'style' => 'opacity: 0;',
				'onchange' => 'readURLforyou(this);',
			),
			'options' => array(
            ),
		));
		
		$this->add(array(
			'name'	=> 'carrier_forafriend_logo',
			'attributes' => array(
				'id' => 'carrier_forafriend_logo',
				'type'  => 'file',
				'class' => 'input-file uniform_on',
				'style' => 'opacity: 0;',
				'onchange' => 'readURLforafriend(this);',
			),
			'options' => array(
            ),
		));
		
		$this->add(array(
			'name'	=> 'carrier_ask_logo',
			'attributes' => array(
				'id' => 'carrier_ask_logo',
				'type'  => 'file',
				'class' => 'input-file uniform_on',
				'style' => 'opacity: 0;',
				'onchange' => 'readURLask(this);',
			),
			'options' => array(
            ),
		));
		
		$this->add(array(
			'name'	=> 'carrier_topbanner',
			'attributes' => array(
				'id' => 'carrier_topbanner',
				'type'  => 'file',
				'class' => 'input-file uniform_on',
				'style' => 'opacity: 0;',
			),
			'options' => array(
            ),
		));
		
		$this->add(array(
            'name' => 'topbannerflag',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'carrier_timezone',
            'options' => array(
                'value_options' => array(
                    "" => "Select Time Zone",
					"-12.00" => "(GMT -12:00) Eniwetok, Kwajalein",
					"-11.00" => "(GMT -11:00) Midway Island, Samoa",
					"-10.00" => "(GMT -10:00) Hawaii",
					"-9.00" => "(GMT -9:00) Alaska",
					"-8.00" => "(GMT -8:00) Pacific Time (US & Canada)",
					"-7.00" => "(GMT -7:00) Mountain Time (US & Canada)",
					"-6.00" => "(GMT -6:00) Central Time (US & Canada), Mexico City",
					"-5.00" => "(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima",
					"-4.00" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
					"-3.30" => "(GMT -3:30) Newfoundland",
					"-3.00" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
					"-2.00" => "(GMT -2:00) Mid-Atlantic",
					"-1.00" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
					"0.00" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
					"1.00" => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris",
					"2.00" => "(GMT +2:00) Kaliningrad, South Africa",
					"3.00" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
					"3.30" => "(GMT +3:30) Tehran",
					"4.00" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
					"4.30" => "(GMT +4:30) Kabul",
					"5.00" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
					"5.30" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
					"5.45" => "(GMT +5:45) Kathmandu",
					"6.00" => "(GMT +6:00) Almaty, Dhaka, Colombo",
					"7.00" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
					"8.00" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
					"9.00" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
					"9.30" => "(GMT +9:30) Adelaide, Darwin",
					"10.00" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
					"11.00" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
					"12.00" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka"
                ),
            ),
            'attributes' => array(
                'value' => ''
            )
        ));
		
		/*	$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'carrier_language',
            'attributes' => array(
                'value' => ''
            )
        ));	*/
		
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
				'id'	=> 'submit_id',
                'type'  => 'submit',
                'value' => 'Save Settings',
				'class'	=> 'btn btn-primary',
            ),
        ));
		
        $this->add(array(
            'name' => 'preview',
            'attributes' => array(
                'type'  => 'button',
                'value' => 'Show Preview',
				'class'	=> 'btn btn-primary',
            ),
        ));
		
    }
}
?>