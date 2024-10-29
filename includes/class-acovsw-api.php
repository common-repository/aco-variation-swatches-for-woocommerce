<?php

if (!defined('ABSPATH'))
    exit;

class ACOVSW_Api
{

    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;
    private $_active = false;

    public function __construct()
    {
        add_action('rest_api_init', function () {

            register_rest_route('acovsw/v1', '/acovswattributes/', array(
                'methods' => 'GET',
                'callback' => array($this, 'pluginAttributes'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('acovsw/v1', '/acovswattributes/', array(
                'methods' => 'POST',
                'callback' => array($this, 'pluginAttributes'),
                'permission_callback' => array($this, 'get_permission'),
            ));

            register_rest_route('acovsw/v1', '/acovswsettings/', array(
                'methods' => 'POST',
                'callback' => array($this, 'pluginSettings'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('acovsw/v1', '/acovswsettings/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'pluginSettings'),
                'permission_callback' => array($this, 'get_permission')
            ));
            
            register_rest_route('acovsw/v1', '/acovswcustomattributes/', array(
                'methods' => 'GET',
                'callback' => array($this, 'pluginCustomAttributes'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('acovsw/v1', '/acovswcustomattributes/', array(
                'methods' => 'POST',
                'callback' => array($this, 'pluginCustomAttributes'),
                'permission_callback' => array($this, 'get_permission'),
            ));

        });
    }

    /**
     *
     * Ensures only one instance of ACOVSW is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main ACOVSW instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    function pluginSettings($data)
    {
            
        $data = $data->get_params(); 

        if( ! $data['id'] ) {  

            $attributeFontSize          = isset($data['settings']['attributeFontSize']) ? $data['settings']['attributeFontSize'] : '';

            $labelHeight                = isset($data['settings']['labelHeight']) ? $data['settings']['labelHeight'] : '';
            $labelLineHeight            = isset($data['settings']['labelLineHeight']) ? $data['settings']['labelLineHeight'] : '';
            $labelType                  = isset($data['settings']['labelType']) ? $data['settings']['labelType'] : '';
            $labelBorderRadius          = isset($data['settings']['labelBorderRadius']) ? $data['settings']['labelBorderRadius'] : '';
            $labelFontSize              = isset($data['settings']['labelFontSize']) ? $data['settings']['labelFontSize'] : '';
            $labeltextColor             = isset($data['settings']['labeltextColor']) ? $data['settings']['labeltextColor'] : '';
            $labeltextColorHex          = isset($data['settings']['labeltextColorHex']) ? $data['settings']['labeltextColorHex'] : '';
            $labeltextHoverColor        = isset($data['settings']['labeltextHoverColor']) ? $data['settings']['labeltextHoverColor'] : '';
            $labeltextHoverColorHex     = isset($data['settings']['labeltextHoverColorHex']) ? $data['settings']['labeltextHoverColorHex'] : '';

            $labeltextSelectedColor     = isset($data['settings']['labeltextSelectedColor']) ? $data['settings']['labeltextSelectedColor'] : '';
            $labeltextSelectedColorHex  = isset($data['settings']['labeltextSelectedColorHex']) ? $data['settings']['labeltextSelectedColorHex'] : '';
            $buttonBGSelectedColor      = isset($data['settings']['buttonBGSelectedColor']) ? $data['settings']['buttonBGSelectedColor'] : '';
            $buttonBGSelectedColorHex   = isset($data['settings']['buttonBGSelectedColorHex']) ? $data['settings']['buttonBGSelectedColorHex'] : '';
            $borderSelectedColor        = isset($data['settings']['borderSelectedColor']) ? $data['settings']['borderSelectedColor'] : '';
            $borderSelectedColorHex     = isset($data['settings']['borderSelectedColorHex']) ? $data['settings']['borderSelectedColorHex'] : '';

            $buttonBGColor              = isset($data['settings']['buttonBGColor']) ? $data['settings']['buttonBGColor'] : '';
            $buttonBGColorHex           = isset($data['settings']['buttonBGColorHex']) ? $data['settings']['buttonBGColorHex'] : '';
            $buttonBGHoverColor         = isset($data['settings']['buttonBGHoverColor']) ? $data['settings']['buttonBGHoverColor'] : '';
            $buttonBGHoverColorHex      = isset($data['settings']['buttonBGHoverColorHex']) ? $data['settings']['buttonBGHoverColorHex'] : '';
            $borderColor                = isset($data['settings']['borderColor']) ? $data['settings']['borderColor'] : '';
            $borderColorHex             = isset($data['settings']['borderColorHex']) ? $data['settings']['borderColorHex'] : '';
            $borderHoverColor           = isset($data['settings']['borderHoverColor']) ? $data['settings']['borderHoverColor'] : '';
            $borderHoverColorHex        = isset($data['settings']['borderHoverColorHex']) ? $data['settings']['borderHoverColorHex'] : '';

            $radiotextColor             = isset($data['settings']['radiotextColor']) ? $data['settings']['radiotextColor'] : '';
            $radiotextColorHex          = isset($data['settings']['radiotextColorHex']) ? $data['settings']['radiotextColorHex'] : '';
            $radioborderColor           = isset($data['settings']['radioborderColor']) ? $data['settings']['radioborderColor'] : '';
            $radioborderColorHex        = isset($data['settings']['radioborderColorHex']) ? $data['settings']['radioborderColorHex'] : '';
            $radiotextHoverColor        = isset($data['settings']['radiotextHoverColor']) ? $data['settings']['radiotextHoverColor'] : '';
            $radiotextHoverColorHex     = isset($data['settings']['radiotextHoverColorHex']) ? $data['settings']['radiotextHoverColorHex'] : '';
            $radioHoverOpacity          = isset($data['settings']['radioHoverOpacity']) ? $data['settings']['radioHoverOpacity'] : '';
            $radioborderHoverColor      = isset($data['settings']['radioborderHoverColor']) ? $data['settings']['radioborderHoverColor'] : '';
            $radioborderHoverColorHex   = isset($data['settings']['radioborderHoverColorHex']) ? $data['settings']['radioborderHoverColorHex'] : '';
            $radiotextSelectedColor     = isset($data['settings']['radiotextSelectedColor']) ? $data['settings']['radiotextSelectedColor'] : '';
            $radiotextSelectedColorHex  = isset($data['settings']['radiotextSelectedColorHex']) ? $data['settings']['radiotextSelectedColorHex'] : '';
            $radioSelectedOpacity       = isset($data['settings']['radioSelectedOpacity']) ? $data['settings']['radioSelectedOpacity'] : '';
            $radioborderSelectedColor   = isset($data['settings']['radioborderSelectedColor']) ? $data['settings']['radioborderSelectedColor'] : '';
            $radioborderSelectedColorHex = isset($data['settings']['radioborderSelectedColorHex']) ? $data['settings']['radioborderSelectedColorHex'] : '';

            $colorHeight                = isset($data['settings']['colorHeight']) ? $data['settings']['colorHeight'] : '';
            $colorWidth                 = isset($data['settings']['colorWidth']) ? $data['settings']['colorWidth'] : '';
            $colorType                  = isset($data['settings']['colorType']) ? $data['settings']['colorType'] : '';
            $colorBorderRadius          = isset($data['settings']['colorBorderRadius']) ? $data['settings']['colorBorderRadius'] : '';
            $colorBorderColor           = isset($data['settings']['colorBorderColor']) ? $data['settings']['colorBorderColor'] : '';
            $colorBorderColorHex        = isset($data['settings']['colorBorderColorHex']) ? $data['settings']['colorBorderColorHex'] : '';
            $colorborderHoverColor      = isset($data['settings']['colorborderHoverColor']) ? $data['settings']['colorborderHoverColor'] : '';
            $colorborderHoverColorHex   = isset($data['settings']['colorborderHoverColorHex']) ? $data['settings']['colorborderHoverColorHex'] : '';
            $colorHoverOpacity          = isset($data['settings']['colorHoverOpacity']) ? $data['settings']['colorHoverOpacity'] : '';
            $colorSelectedOpacity       = isset($data['settings']['colorSelectedOpacity']) ? $data['settings']['colorSelectedOpacity'] : '';
            $colorborderSelectedColor   = isset($data['settings']['colorborderSelectedColor']) ? $data['settings']['colorborderSelectedColor'] : '';
            $colorborderSelectedColorHex = isset($data['settings']['colorborderSelectedColorHex']) ? $data['settings']['colorborderSelectedColorHex'] : '';

            $imageHeight                = isset($data['settings']['imageHeight']) ? $data['settings']['imageHeight'] : '';
            $imageWidth                 = isset($data['settings']['imageWidth']) ? $data['settings']['imageWidth'] : '';
            $imageType                  = isset($data['settings']['imageType']) ? $data['settings']['imageType'] : '';
            $imageBorderRadius          = isset($data['settings']['imageBorderRadius']) ? $data['settings']['imageBorderRadius'] : '';
            $imageBorderColor           = isset($data['settings']['imageBorderColor']) ? $data['settings']['imageBorderColor'] : '';
            $imageBorderColorHex        = isset($data['settings']['imageBorderColorHex']) ? $data['settings']['imageBorderColorHex'] : '';
            $imageBorderHoverColor      = isset($data['settings']['imageBorderHoverColor']) ? $data['settings']['imageBorderHoverColor'] : '';
            $imageBorderHoverColorHex   = isset($data['settings']['imageBorderHoverColorHex']) ? $data['settings']['imageBorderHoverColorHex'] : '';
            $imageHoverOpacity          = isset($data['settings']['imageHoverOpacity']) ? $data['settings']['imageHoverOpacity'] : '';
            $imageBorderSelectedColor   = isset($data['settings']['imageBorderSelectedColor']) ? $data['settings']['imageBorderSelectedColor'] : '';
            $imageBorderSelectedColorHex = isset($data['settings']['imageBorderSelectedColorHex']) ? $data['settings']['imageBorderSelectedColorHex'] : '';
            $imageSelectedOpacity       = isset($data['settings']['imageSelectedOpacity']) ? $data['settings']['imageSelectedOpacity'] : '';

            $enableTooltip              = isset($data['settings']['enableTooltip']) ? $data['settings']['enableTooltip'] : '';
            $tooltipFontSize            = isset($data['settings']['tooltipFontSize']) ? $data['settings']['tooltipFontSize'] : '';
            $tooltipBorderRadius        = isset($data['settings']['tooltipBorderRadius']) ? $data['settings']['tooltipBorderRadius'] : '';
            $tooltipPosition            = isset($data['settings']['tooltipPosition']) ? $data['settings']['tooltipPosition'] : '';
            $toolTipTextColor           = isset($data['settings']['toolTipTextColor']) ? $data['settings']['toolTipTextColor'] : '';
            $toolTipTextColorHex        = isset($data['settings']['toolTipTextColorHex']) ? $data['settings']['toolTipTextColorHex'] : '';
            $toolTipBorderColor         = isset($data['settings']['toolTipBorderColor']) ? $data['settings']['toolTipBorderColor'] : '';
            $toolTipBorderColorHex      = isset($data['settings']['toolTipBorderColorHex']) ? $data['settings']['toolTipBorderColorHex'] : '';
            $toolTipBGColor             = isset($data['settings']['toolTipBGColor']) ? $data['settings']['toolTipBGColor'] : '';
            $toolTipBGColorHex          = isset($data['settings']['toolTipBGColorHex']) ? $data['settings']['toolTipBGColorHex'] : '';

            $outOfStock                 = isset($data['settings']['outOfStock']) ? $data['settings']['outOfStock'] : '';

            $common_settings = array(
                'attributeFontSize' => $attributeFontSize
            );
            
            $label_settings = array(
                'labelHeight'           => ( $labelHeight != '' && $labelHeight < 0 ) ? ( $labelHeight * -1 ) : $labelHeight,
                'labelLineHeight'       => ( $labelLineHeight != '' && $labelLineHeight < 0 ) ? ( $labelLineHeight * -1 ) : $labelLineHeight,
                'labelType'             => $labelType,
                'labelBorderRadius'     => $labelBorderRadius,
                'labelFontSize'         => ( $labelFontSize != '' && $labelFontSize < 0 ) ? ( $labelFontSize * -1 ) : $labelFontSize,
                'labeltextColor'        => $labeltextColor,
                'labeltextColorHex'     => $labeltextColorHex,
                'buttonBGColor'         => $buttonBGColor,
                'buttonBGColorHex'      => $buttonBGColorHex,
                'borderColor'           => $borderColor,
                'borderColorHex'        => $borderColorHex,
                'labeltextHoverColor'   => $labeltextHoverColor,
                'labeltextHoverColorHex' => $labeltextHoverColorHex,
                'buttonBGHoverColor'    => $buttonBGHoverColor,
                'buttonBGHoverColorHex' => $buttonBGHoverColorHex,
                'borderHoverColor'      => $borderHoverColor,
                'borderHoverColorHex'   => $borderHoverColorHex,
                'labeltextSelectedColor' => $labeltextSelectedColor,
                'labeltextSelectedColorHex' => $labeltextSelectedColorHex,
                'buttonBGSelectedColor' => $buttonBGSelectedColor,
                'buttonBGSelectedColorHex' => $buttonBGSelectedColorHex,
                'borderSelectedColor'   => $borderSelectedColor,
                'borderSelectedColorHex' => $borderSelectedColorHex,
            );

            $radio_settings = array(
                'radiotextColor'            => $radiotextColor,
                'radiotextColorHex'         => $radiotextColorHex,
                'radioborderColor'          => $radioborderColor,
                'radioborderColorHex'       => $radioborderColorHex,
                'radiotextHoverColor'       => $radiotextHoverColor,
                'radiotextHoverColorHex'    => $radiotextHoverColorHex,
                'radioHoverOpacity'         => ( $radioHoverOpacity != '' && $radioHoverOpacity < 0 ) ? ( $radioHoverOpacity * -1 ) : $radioHoverOpacity,
                'radioborderHoverColor'     => $radioborderHoverColor,
                'radioborderHoverColorHex'  => $radioborderHoverColorHex,
                'radiotextSelectedColor'    => $radiotextSelectedColor,
                'radiotextSelectedColorHex' => $radiotextSelectedColorHex,
                'radioSelectedOpacity'      => ( $radioSelectedOpacity != '' && $radioSelectedOpacity < 0 ) ? ( $radioSelectedOpacity * -1 ) : $radioSelectedOpacity,
                'radioborderSelectedColor'  => $radioborderSelectedColor,
                'radioborderSelectedColorHex' => $radioborderSelectedColorHex,
            );

            $color_settings = array(
                'colorHeight'               => ( $colorHeight != '' && $colorHeight < 0 ) ? ( $colorHeight * -1 ) : $colorHeight,
                'colorWidth'                => ( $colorWidth != '' && $colorWidth < 0 ) ? ( $colorWidth * -1 ) : $colorWidth,
                'colorType'                 => $colorType,
                'colorBorderRadius'         => ( $colorBorderRadius != '' && $colorBorderRadius < 0 ) ? ( $colorBorderRadius * -1 ) : $colorBorderRadius,
                'colorBorderColor'          => $colorBorderColor,
                'colorBorderColorHex'       => $colorBorderColorHex,
                'colorborderHoverColor'     => $colorborderHoverColor,
                'colorborderHoverColorHex'  => $colorborderHoverColorHex,
                'colorHoverOpacity'         => ( $colorHoverOpacity != '' && $colorHoverOpacity < 0 ) ? ( $colorHoverOpacity * -1 ) : $colorHoverOpacity,
                'colorSelectedOpacity'      => ( $colorSelectedOpacity != '' && $colorSelectedOpacity < 0 ) ? ( $colorSelectedOpacity * -1 ) : $colorSelectedOpacity,
                'colorborderSelectedColor'  => $colorborderSelectedColor,
                'colorborderSelectedColorHex' => $colorborderSelectedColorHex,
            );

            $image_settings = array(
                'imageHeight'               => ( $imageHeight != '' && $imageHeight < 0 ) ? ( $imageHeight * -1 ) : $imageHeight,
                'imageWidth'                => ( $imageWidth != '' && $imageWidth < 0 ) ? ( $imageWidth * -1 ) : $imageWidth,
                'imageType'                 => $imageType,
                'imageBorderRadius'         => $imageBorderRadius,
                'imageBorderColor'          => $imageBorderColor,
                'imageBorderColorHex'       => $imageBorderColorHex,
                'imageBorderHoverColor'     => $imageBorderHoverColor,
                'imageBorderHoverColorHex'  => $imageBorderHoverColorHex,
                'imageHoverOpacity'         => ( $imageHoverOpacity != '' && $imageHoverOpacity < 0 ) ? ( $imageHoverOpacity * -1 ) : $imageHoverOpacity,
                'imageBorderSelectedColor'  => $imageBorderSelectedColor,
                'imageBorderSelectedColorHex' => $imageBorderSelectedColorHex,
                'imageSelectedOpacity'      => ( $imageSelectedOpacity != '' && $imageSelectedOpacity < 0 ) ? ( $imageSelectedOpacity * -1 ) : $imageSelectedOpacity,
            );

            $tooltip_settings = array(
                'enableTooltip' => $enableTooltip,
                'tooltipFontSize' => ( $tooltipFontSize != '' && $tooltipFontSize < 0 ) ? ( $tooltipFontSize * -1 ) : $tooltipFontSize,
                'tooltipPosition' => $tooltipPosition,
                'tooltipBorderRadius' => ( $tooltipBorderRadius != '' && $tooltipBorderRadius < 0 ) ? ( $tooltipBorderRadius * -1 ) : $tooltipBorderRadius,
                'toolTipTextColor' => $toolTipTextColor,
                'toolTipTextColorHex' => $toolTipTextColorHex,
                'toolTipBGColor' => $toolTipBGColor,
                'toolTipBGColorHex' => $toolTipBGColorHex,
                'toolTipBorderColor' => $toolTipBorderColor,
                'toolTipBorderColorHex' => $toolTipBorderColorHex,
            ); 
            
            $advanced_settings = array(
                'outOfStock' => $outOfStock,
            );

            if ( false === get_option('acovsw_common_settings') )
                add_option('acovsw_common_settings', $common_settings, '', 'yes');
            else
                update_option('acovsw_common_settings', $common_settings);

            if ( false === get_option('acovsw_label_settings') )
                add_option('acovsw_label_settings', $label_settings, '', 'yes');
            else
                update_option('acovsw_label_settings', $label_settings);

            if ( false === get_option('acovsw_radio_settings') )
                add_option('acovsw_radio_settings', $radio_settings, '', 'yes');
            else
                update_option('acovsw_radio_settings', $radio_settings);

            if ( false === get_option('acovsw_color_settings') )
                add_option('acovsw_color_settings', $color_settings, '', 'yes');
            else
                update_option('acovsw_color_settings', $color_settings);

            if ( false === get_option('acovsw_image_settings') )
                add_option('acovsw_image_settings', $image_settings, '', 'yes');
            else
                update_option('acovsw_image_settings', $image_settings);

            if ( false === get_option('acovsw_tooltip_settings') )
                add_option('acovsw_tooltip_settings', $tooltip_settings, '', 'yes');
            else
                update_option('acovsw_tooltip_settings', $tooltip_settings);

            if ( false === get_option('acovsw_advanced_settings') )
                add_option('acovsw_advanced_settings', $advanced_settings, '', 'yes');
            else
                update_option('acovsw_advanced_settings', $advanced_settings);

        }

        // Common Settings
        $common_settings = get_option('acovsw_common_settings') ? get_option('acovsw_common_settings') : [];
        $result['attributeFontSize'] = $common_settings['attributeFontSize'];
        
        // Label Settings
        $label_settings = get_option('acovsw_label_settings') ? get_option('acovsw_label_settings') : [];
        $result['labelHeight'] = array_key_exists ( 'labelHeight', $label_settings ) ? $label_settings['labelHeight'] : '';
        $result['labelLineHeight'] = array_key_exists ( 'labelLineHeight', $label_settings ) ? $label_settings['labelLineHeight'] : '';
        $result['labelType'] = array_key_exists ( 'labelType', $label_settings ) ? $label_settings['labelType'] : '';
        $result['labelBorderRadius'] = array_key_exists ( 'labelBorderRadius', $label_settings ) ? $label_settings['labelBorderRadius'] : '';
        $result['labelFontSize'] = array_key_exists ( 'labelFontSize', $label_settings ) ? $label_settings['labelFontSize'] : '';
        $result['labeltextColor'] = array_key_exists ( 'labeltextColor', $label_settings ) ? $label_settings['labeltextColor'] : '';
        $result['labeltextColorHex'] = array_key_exists ( 'labeltextColorHex', $label_settings ) ? $label_settings['labeltextColorHex'] : '';
        $result['buttonBGColor'] = array_key_exists ( 'buttonBGColor', $label_settings ) ? $label_settings['buttonBGColor'] : '';
        $result['buttonBGColorHex'] = array_key_exists ( 'buttonBGColorHex', $label_settings ) ? $label_settings['buttonBGColorHex'] : '';
        $result['borderColor'] = array_key_exists ( 'borderColor', $label_settings ) ? $label_settings['borderColor'] : '';
        $result['borderColorHex'] = array_key_exists ( 'borderColorHex', $label_settings ) ? $label_settings['borderColorHex'] : '';
        $result['labeltextHoverColor'] = array_key_exists ( 'labeltextHoverColor', $label_settings ) ? $label_settings['labeltextHoverColor'] : '';
        $result['labeltextHoverColorHex'] = array_key_exists ( 'labeltextHoverColorHex', $label_settings ) ? $label_settings['labeltextHoverColorHex'] : '';
        $result['buttonBGHoverColor'] = array_key_exists ( 'buttonBGHoverColor', $label_settings ) ? $label_settings['buttonBGHoverColor'] : '';
        $result['buttonBGHoverColorHex'] = array_key_exists ( 'buttonBGHoverColorHex', $label_settings ) ? $label_settings['buttonBGHoverColorHex'] : '';
        $result['borderHoverColor'] = array_key_exists ( 'borderHoverColor', $label_settings ) ? $label_settings['borderHoverColor'] : '';
        $result['borderHoverColorHex'] = array_key_exists ( 'borderHoverColorHex', $label_settings ) ? $label_settings['borderHoverColorHex'] : '';
        $result['labeltextSelectedColor'] = array_key_exists ( 'labeltextSelectedColor', $label_settings ) ? $label_settings['labeltextSelectedColor'] : '';
        $result['labeltextSelectedColorHex'] = array_key_exists ( 'labeltextSelectedColorHex', $label_settings ) ? $label_settings['labeltextSelectedColorHex'] : '';
        $result['buttonBGSelectedColor'] = array_key_exists ( 'buttonBGSelectedColor', $label_settings ) ? $label_settings['buttonBGSelectedColor'] : '';
        $result['buttonBGSelectedColorHex'] = array_key_exists ( 'buttonBGSelectedColorHex', $label_settings ) ? $label_settings['buttonBGSelectedColorHex'] : '';
        $result['borderSelectedColor'] = array_key_exists ( 'borderSelectedColor', $label_settings ) ? $label_settings['borderSelectedColor'] : '';
        $result['borderSelectedColorHex'] = array_key_exists ( 'borderSelectedColorHex', $label_settings ) ? $label_settings['borderSelectedColorHex'] : '';

        $radio_settings = get_option('acovsw_radio_settings') ? get_option('acovsw_radio_settings') : [];
        $result['radiotextColor'] = array_key_exists ( 'radiotextColor', $radio_settings ) ? $radio_settings['radiotextColor'] : '';
        $result['radiotextColorHex'] = array_key_exists ( 'radiotextColorHex', $radio_settings ) ? $radio_settings['radiotextColorHex'] : '';
        $result['radioborderColor'] = array_key_exists ( 'radioborderColor', $radio_settings ) ? $radio_settings['radioborderColor'] : '';
        $result['radioborderColorHex'] = array_key_exists ( 'radioborderColorHex', $radio_settings ) ? $radio_settings['radioborderColorHex'] : '';
        $result['radiotextHoverColor'] = array_key_exists ( 'radiotextHoverColor', $radio_settings ) ? $radio_settings['radiotextHoverColor'] : '';
        $result['radiotextHoverColorHex'] = array_key_exists ( 'radiotextHoverColorHex', $radio_settings ) ? $radio_settings['radiotextHoverColorHex'] : '';
        $result['radioHoverOpacity'] = array_key_exists ( 'radioHoverOpacity', $radio_settings ) ? $radio_settings['radioHoverOpacity'] : 90;
        $result['radioborderHoverColor'] = array_key_exists ( 'radioborderHoverColor', $radio_settings ) ? $radio_settings['radioborderHoverColor'] : '';
        $result['radioborderHoverColorHex'] = array_key_exists ( 'radioborderHoverColorHex', $radio_settings ) ? $radio_settings['radioborderHoverColorHex'] : '';
        $result['radiotextSelectedColor'] = array_key_exists ( 'radiotextSelectedColor', $radio_settings ) ? $radio_settings['radiotextSelectedColor'] : '';
        $result['radiotextSelectedColorHex'] = array_key_exists ( 'radiotextSelectedColorHex', $radio_settings ) ? $radio_settings['radiotextSelectedColorHex'] : '';
        $result['radioSelectedOpacity'] = array_key_exists ( 'radioSelectedOpacity', $radio_settings ) ? $radio_settings['radioSelectedOpacity'] : 75;
        $result['radioborderSelectedColor'] = array_key_exists ( 'radioborderSelectedColor', $radio_settings ) ? $radio_settings['radioborderSelectedColor'] : '';
        $result['radioborderSelectedColorHex'] = array_key_exists ( 'radioborderSelectedColorHex', $radio_settings ) ? $radio_settings['radioborderSelectedColorHex'] : '';

        $color_settings = get_option('acovsw_color_settings') ? get_option('acovsw_color_settings') : [];
        $result['colorHeight'] = array_key_exists ( 'colorHeight', $color_settings ) ? $color_settings['colorHeight'] : '';
        $result['colorWidth'] = array_key_exists ( 'colorWidth', $color_settings ) ? $color_settings['colorWidth'] : '';
        $result['colorType'] = array_key_exists ( 'colorType', $color_settings ) ? $color_settings['colorType'] : '';
        $result['colorBorderRadius'] = array_key_exists ( 'colorBorderRadius', $color_settings ) ? $color_settings['colorBorderRadius'] : '';
        $result['colorBorderColor'] = array_key_exists ( 'colorBorderColor', $color_settings ) ? $color_settings['colorBorderColor'] : '';
        $result['colorBorderColorHex'] = array_key_exists ( 'colorBorderColorHex', $color_settings ) ? $color_settings['colorBorderColorHex'] : '';
        $result['colorborderHoverColor'] = array_key_exists ( 'colorborderHoverColor', $color_settings ) ? $color_settings['colorborderHoverColor'] : '';
        $result['colorborderHoverColorHex'] = array_key_exists ( 'colorborderHoverColorHex', $color_settings ) ? $color_settings['colorborderHoverColorHex'] : '';
        $result['colorHoverOpacity'] = array_key_exists ( 'colorHoverOpacity', $color_settings ) ? $color_settings['colorHoverOpacity'] : 90;
        $result['colorSelectedOpacity'] = array_key_exists ( 'colorSelectedOpacity', $color_settings ) ? $color_settings['colorSelectedOpacity'] : 75;
        $result['colorborderSelectedColor'] = array_key_exists ( 'colorborderSelectedColor', $color_settings ) ? $color_settings['colorborderSelectedColor'] : '';
        $result['colorborderSelectedColorHex'] = array_key_exists ( 'colorborderSelectedColorHex', $color_settings ) ? $color_settings['colorborderSelectedColorHex'] : '';

        $image_settings = get_option('acovsw_image_settings') ? get_option('acovsw_image_settings') : [];
        $result['imageHeight'] = array_key_exists ( 'imageHeight', $image_settings ) ? $image_settings['imageHeight'] : '';
        $result['imageWidth'] = array_key_exists ( 'imageWidth', $image_settings ) ? $image_settings['imageWidth'] : '';
        $result['imageType'] = array_key_exists ( 'imageType', $image_settings ) ? $image_settings['imageType'] : '';
        $result['imageBorderRadius'] = array_key_exists ( 'imageBorderRadius', $image_settings ) ? $image_settings['imageBorderRadius'] : '';
        $result['imageBorderColor'] = array_key_exists ( 'imageBorderColor', $image_settings ) ? $image_settings['imageBorderColor'] : '';
        $result['imageBorderColorHex'] = array_key_exists ( 'imageBorderColorHex', $image_settings ) ? $image_settings['imageBorderColorHex'] : '';
        $result['imageBorderHoverColor'] = array_key_exists ( 'imageBorderHoverColor', $image_settings ) ? $image_settings['imageBorderHoverColor'] : '';
        $result['imageBorderHoverColorHex'] = array_key_exists ( 'imageBorderHoverColorHex', $image_settings ) ? $image_settings['imageBorderHoverColorHex'] : '';
        $result['imageHoverOpacity'] = array_key_exists ( 'imageHoverOpacity', $image_settings ) ? $image_settings['imageHoverOpacity'] : 90;
        $result['imageBorderSelectedColor'] = array_key_exists ( 'imageBorderSelectedColor', $image_settings ) ? $image_settings['imageBorderSelectedColor'] : '';
        $result['imageBorderSelectedColorHex'] = array_key_exists ( 'imageBorderSelectedColorHex', $image_settings ) ? $image_settings['imageBorderSelectedColorHex'] : '';
        $result['imageSelectedOpacity'] = array_key_exists ( 'imageSelectedOpacity', $image_settings ) ? $image_settings['imageSelectedOpacity'] : 75;

        // Tooltip Settings
        $tooltip_settings = get_option('acovsw_tooltip_settings') ? get_option('acovsw_tooltip_settings') : [];
        $result['enableTooltip'] = array_key_exists ( 'enableTooltip', $tooltip_settings ) ? $tooltip_settings['enableTooltip'] : '';
        $result['tooltipFontSize'] = array_key_exists ( 'tooltipFontSize', $tooltip_settings ) ? $tooltip_settings['tooltipFontSize'] : '';
        $result['tooltipPosition'] = array_key_exists ( 'tooltipPosition', $tooltip_settings ) ? $tooltip_settings['tooltipPosition'] : '';
        $result['tooltipBorderRadius'] = array_key_exists ( 'tooltipBorderRadius', $tooltip_settings ) ? $tooltip_settings['tooltipBorderRadius'] : '';
        $result['toolTipTextColor'] = array_key_exists ( 'toolTipTextColor', $tooltip_settings ) ? $tooltip_settings['toolTipTextColor'] : '';
        $result['toolTipTextColorHex'] = array_key_exists ( 'toolTipTextColorHex', $tooltip_settings ) ? $tooltip_settings['toolTipTextColorHex'] : '';
        $result['textHoverColor'] = array_key_exists ( 'textHoverColor', $tooltip_settings ) ? $tooltip_settings['textHoverColor'] : '';
        $result['textHoverColorHex'] = array_key_exists ( 'textHoverColorHex', $tooltip_settings ) ? $tooltip_settings['textHoverColorHex'] : '';
        $result['toolTipBGColor'] = array_key_exists ( 'toolTipBGColor', $tooltip_settings ) ? $tooltip_settings['toolTipBGColor'] : '';
        $result['toolTipBGColorHex'] = array_key_exists ( 'toolTipBGColorHex', $tooltip_settings ) ? $tooltip_settings['toolTipBGColorHex'] : '';
        $result['toolTipBorderColor'] = array_key_exists ( 'toolTipBorderColor', $tooltip_settings ) ? $tooltip_settings['toolTipBorderColor'] : '';
        $result['toolTipBorderColorHex'] = array_key_exists ( 'toolTipBorderColorHex', $tooltip_settings ) ? $tooltip_settings['toolTipBorderColorHex'] : '';

        // Advanced Settings
        $advanced_settings = get_option('acovsw_advanced_settings') ? get_option('acovsw_advanced_settings') : [];
        $result['outOfStock'] = array_key_exists ( 'outOfStock', $advanced_settings ) ? $advanced_settings['outOfStock'] : '';

        return new WP_REST_Response($result, 200);

    }

    public function delete_transient()
    {
        delete_transient(ACOVSW_PRODUCTS_TRANSIENT_KEY);
    }


    function pluginAttributes($data)
    {
        $data = $data->get_params(); 

        $result = array();

        if ( isset ( $data['attrtype'] ) ) { 

            $attribute_type = $data['attrtype'];
            // $attribute_name = $data['attributename'];
            $attribute_index = $data['attrindex']; 

            $newData = $data['items']; 

            if ( false === get_option ( 'acovsw_attribute_values' ) ) {
                add_option ( 'acovsw_attribute_values', $newData, '', 'yes' );
            } else {
                $currentData = get_option ( 'acovsw_attribute_values' );
                $index = $attribute_index;
                if ( $index !== false ) {
                    $currentData[$index] = $newData[$attribute_index];
                }
                update_option ( 'acovsw_attribute_values', $currentData );
            }

            return '';

        }

        $attributes = wc_get_attribute_taxonomies();
        if ( $attributes ) {
            // $attributes = array_reverse ($attributes); // To display the attributes in ascending order
            $savedData = get_option ( 'acovsw_attribute_values' ) ? get_option ( 'acovsw_attribute_values' ) : [];
            foreach ( $attributes as $attribute ) { 
                $attr_style         = 'horizontal'; 
                $attr_type          = 'default';
                $attr_iconHeight    = $attr_iconWidth = $attr_image = $attr_color = '';
                $attr_id            = $attribute->attribute_id;
                $attr_label         = $attribute->attribute_label;
                $attr_name          = $attribute->attribute_name;
                $index              = $savedData ? $this->acovsw_array_search_multi ( $attr_name, $savedData ) : false; 

                $terms = get_terms ( array(
                    'taxonomy' => 'pa_'.$attribute->attribute_name,
                    'hide_empty' => false,
                ) );

                if ( $index !== false && array_key_exists ( $index, $savedData ) ) {
                    $attr_style         = array_key_exists ( 'attr_style', $savedData[$index] ) ? $savedData[$index]['attr_style'] : 'horizontal';
                    $attr_type          = array_key_exists ( 'attr_type', $savedData[$index] ) ? $savedData[$index]['attr_type'] : 'default';
                    $attr_iconHeight    = array_key_exists ( 'attr_iconHeight', $savedData[$index] ) ? $savedData[$index]['attr_iconHeight'] : '';
                    $attr_iconWidth     = array_key_exists ( 'attr_iconWidth', $savedData[$index] ) ? $savedData[$index]['attr_iconWidth'] : '';
                    $attr_image         = array_key_exists ( 'attr_image', $savedData[$index] ) ? $savedData[$index]['attr_image'] : [];
                    $attr_color         = array_key_exists ( 'attr_color', $savedData[$index] ) ? $savedData[$index]['attr_color'] : [];
                }
                $result[$attr_id] = array ( 'attr_id' => $attr_id, 'attr_label' => $attr_label, 'attr_name' => $attr_name, 'attr_style' => $attr_style, 'attr_type' => $attr_type, 'attr_image' => $attr_image, 'attr_color' => $attr_color, 'attr_iconHeight' => $attr_iconHeight, 'attr_iconWidth' => $attr_iconWidth, 'terms' => $terms );
            }

            // Sorting array based on attribute ids
            // usort($result, function($a, $b) {
            //     return $a['attr_id'] - $b['attr_id'];
            // });

        }

        return new WP_REST_Response($result, 200);

    }

    function pluginCustomAttributes($data)
    {
        $data       = $data->get_params(); 
        $id         = $data['id'];

        $product    = get_product($id);

        if ( !$product ) return '';

        $attributes = $product->get_attributes();   
        
        $result = array();

        if ( $attributes ) {

            if ( isset ( $data['attrtype'] ) ) { 

                $attribute_type = $data['attrtype'];
                // $attribute_name = $data['attributename'];
                $attribute_index = $data['attrindex']; 

                $newData = $data['items']; 

                if ( false === get_post_meta ( $id, 'acovsw_custom_settings', true ) ) {
                    add_post_meta ( $id, 'acovsw_custom_settings', $newData, true );
                } else { 
                    $currentData = get_post_meta ( $id, 'acovsw_custom_settings', true );
                    $index = $currentData ? $attribute_index : false; 
                    if ( $index !== false ) { 
                        $currentData[$index] = $newData[$attribute_index];
                    } else {
                        $currentData = $newData;
                    } 
                    update_post_meta ( $id, 'acovsw_custom_settings', $currentData );
                }

                return '';

            }

            $savedData = get_post_meta ( $id,'acovsw_custom_settings', true ) ? get_post_meta ( $id,'acovsw_custom_settings', true ) : [];

            foreach ( $attributes as $slug=>$attribute ) { 
                if ( $attribute->is_taxonomy() == false ) { 
                    // $attribute_name = wc_attribute_label($attribute); 

                    $attr_style = 'horizontal'; $attr_type = 'default';
                    $attr_iconHeight = $attr_iconWidth = $attr_image = $attr_color = '';
                    $attr_id = $slug;
                    $attr_label = $attribute->get_name();
                    $attr_name = $slug;
                    $index = $savedData ? $this->acovsw_array_search_multi ( $attr_name, $savedData ) : false; 
                    $optns = $attribute->get_options(); 
                    $terms = array();
                    if ( $optns ) {
                        foreach ($optns as $optn) {
                            $slug = str_replace(' ', '-', strtolower( $optn ));
                            $terms[] = array( 'name' => $optn, 'slug' => $slug );
                        }

                        if ( $index !== false ) {
                            $attr_style = array_key_exists ( 'attr_style', $savedData[$index] ) ? $savedData[$index]['attr_style'] : 'horizontal';
                            $attr_type = array_key_exists ( 'attr_type', $savedData[$index] ) ? $savedData[$index]['attr_type'] : 'default';
                            $attr_iconHeight = array_key_exists ( 'attr_iconHeight', $savedData[$index] ) ? $savedData[$index]['attr_iconHeight'] : '';
                            $attr_iconWidth = array_key_exists ( 'attr_iconWidth', $savedData[$index] ) ? $savedData[$index]['attr_iconWidth'] : '';
                            $attr_image = array_key_exists ( 'attr_image', $savedData[$index] ) ? $savedData[$index]['attr_image'] : [];
                            $attr_color = array_key_exists ( 'attr_color', $savedData[$index] ) ? $savedData[$index]['attr_color'] : [];
                        
                        }
                        $result[$attr_id] = array ( 'attr_id' => $attr_id, 'attr_label' => $attr_label, 'attr_name' => $attr_name, 'attr_style' => $attr_style, 'attr_type' => $attr_type, 'attr_image' => $attr_image, 'attr_color' => $attr_color, 'attr_iconHeight' => $attr_iconHeight, 'attr_iconWidth' => $attr_iconWidth, 'terms' => $terms );

                    }
                }
            }
        }

        return new WP_REST_Response($result, 200);

    }

    public function acovsw_array_search_multi ( $needle, array $haystack )
    {
        foreach ( $haystack as $key => $value ) {
            if ( $value['attr_name'] == $needle ) {
                return $key;
            }
        }
        return -1;
    }

    /**
     * Permission Callback
     **/
    public function get_permission()
    {
        if (current_user_can('administrator') || current_user_can('manage_woocommerce')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

}
