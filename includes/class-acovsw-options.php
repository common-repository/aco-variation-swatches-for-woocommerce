<?php

if (!defined('ABSPATH'))
    exit;

class ACOVSW_Options
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
    public $attributelist = [];
    public $customStyles = false;
    private $_active = false;

    public function __construct()
    {



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

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->_active;
    }

    public function acovswAttributesOptionsView ( $view, $args )
    {

        global $product; 

        $productID          = $product->get_id();
        $new_view           = ''; 
        $customType         = false;
        // $items = get_option('acovsw_attribute_values') ? get_option('acovsw_attribute_values') : '';
        $attributeSettings  = get_option('acovsw_attribute_values') ? get_option('acovsw_attribute_values') : [];
        $attribute          = $args['attribute'];
        // $index = array_search ( str_replace('pa_', '', $attribute), array_column ( $attributeSettings, 'attr_name' ) ); 
        $index              = $this->acovsw_array_search_multi ( str_replace('pa_', '', $attribute), $attributeSettings ); 
        // $selectedType       = !empty ( $attributeSettings ) ? ( $index >= 0 ? ( array_key_exists ( 'attr_type', $attributeSettings[$index] ) ? $attributeSettings[$index]['attr_type'] : '' ) : '' ) : '';
        // $selectedStyle      = !empty ( $attributeSettings ) ? ( $index >= 0 ? ( array_key_exists ( 'attr_style', $attributeSettings[$index] ) ? $attributeSettings[$index]['attr_style'] : '' ) : '' ) : '';
        $tooltipOptions     = get_option('acovsw_tooltip_settings') ? get_option('acovsw_tooltip_settings') : [];
        $tooltipEnabled     = array_key_exists( 'enableTooltip', $tooltipOptions ) ? $tooltipOptions['enableTooltip'] : '';
        $tooltipPosition    = array_key_exists( 'tooltipPosition', $tooltipOptions ) ? $tooltipOptions['tooltipPosition'] : '';

        $generalSettings    = get_option('acovsw_common_settings') ? get_option('acovsw_common_settings') : [];
        $labelSettings      = get_option('acovsw_label_settings') ? get_option('acovsw_label_settings') : [];
        $radioSettings      = get_option('acovsw_radio_settings') ? get_option('acovsw_radio_settings') : [];
        $colorSettings      = get_option('acovsw_color_settings') ? get_option('acovsw_color_settings') : [];
        $imageSettings      = get_option('acovsw_image_settings') ? get_option('acovsw_image_settings') : [];
        $advancedSettings   = get_option('acovsw_advanced_settings') ? get_option('acovsw_advanced_settings') : [];

        $outOfStockStyle    = array_key_exists ( 'outOfStock', $advancedSettings ) ? $advancedSettings['outOfStock'] : '';
        $stckClass          = ( $outOfStockStyle == 'cross' ) ? ' acovsw-outstock-cross': ( ( $outOfStockStyle == 'hide' ) ? ' acovsw-outstock-hide': '' );
        $CSSClass           = 'acovsw-attribute-style'.$stckClass;

        // Check type
        $type               = $this->checkType ( $args, $product ); 

		if ( $type == 'select' || $type == '' ) {
			$view = $this->acovswSelectItems ( $args, $CSSClass );
		} 

		if ( ! $attribute || ! taxonomy_exists( $attribute ) ) { // Check for custom attributes
            
            $custom_settings = get_post_meta ( $productID,'acovsw_custom_settings', true ) ? get_post_meta ( $productID,'acovsw_custom_settings', true ) : [];
            if ( empty ( $custom_settings ) ) {
                return $view;
            }

            $cs_attr            = str_replace(' ', '-', strtolower( $attribute ));
            $attributeSettings  = array_key_exists ( $cs_attr, $custom_settings ) ? $custom_settings[$cs_attr] : []; 
			$selectedType       = array_key_exists ( 'attr_type', $attributeSettings ) ? $attributeSettings['attr_type'] : '';
			$customType         = true;
             
		} else {

            $attributeSettings  = get_option('acovsw_attribute_values') ? get_option('acovsw_attribute_values') : [];
            $index              = $this->acovsw_array_search_multi ( str_replace('pa_', '', $attribute), $attributeSettings ); 
            $selectedType       = !empty ( $attributeSettings ) ? ( $index >= 0 ? ( array_key_exists ( 'attr_type', $attributeSettings[$index] ) ? $attributeSettings[$index]['attr_type'] : '' ) : '' ) : '';

        } 

        // Set Custom Styles
        if ( ( $labelSettings || $radioSettings || $colorSettings || $imageSettings || $tooltipEnabled ) && $this->customStyles == '' ) {
            $this->acovswCustomStyles ( $generalSettings, $labelSettings, $radioSettings, $colorSettings, $imageSettings, $tooltipOptions, $tooltipEnabled );
        }
        //

        if ( $selectedType == 'color' )
	        $new_view .= $this->acovswAttrTypeColor ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $attributeSettings, $customType );
        else if ( $selectedType == 'image' )
	        $new_view .= $this->acovswAttrTypeImage ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $attributeSettings, $customType );
        else if ( $selectedType == 'radio' )
	        $new_view .= $this->acovswAttrTypeRadio ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $attributeSettings, $customType );
        else if ( $selectedType == 'label' )
	        $new_view .= $this->acovswAttrTypeLabel ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $labelSettings, $attributeSettings, $customType );

        echo $new_view ? $new_view : $view;

    }

    public function acovswSelectItems ( $args, $CSSClass ) 
    {

		$args                   = wp_parse_args( $args, array( 'options'  => false, 'attribute' => false, 'product' => false, 'selected' => false, 'name' => '', 'id' => '', 'class' => $CSSClass, 'type' => '', 'assigned' => '', 'show_option_none' => __( 'Select an option', 'aco-variation-swatches-for-woocommerce' ) ) );

		$options                = $args['options'] ?: array();
		$product                = $args['product'] ?: null;
		$attribute              = $args['attribute'] ?: '';
		$name                   = $args['name'] ?: 'attribute_' . sanitize_title( $attribute );
		$id                     = $args['id'] ?: sanitize_title( $attribute );
		$class                  = taxonomy_exists( $attribute ) ? 'acovsw-attribute-select' : '';
		$show_option_none       = (bool) $args['show_option_none'] ? 'yes' : 'no';
		$show_option_none_text  = $args['show_option_none'];
        $result                 = '';

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$result  = '<select name="'.esc_attr( $name ).'" id="'.esc_attr( $id ).'" class="'.esc_attr( $class ).'" data-attribute_name="attribute_'.esc_attr( sanitize_title( $attribute ) ).'" data-show_option_none="attribute_'.$show_option_none.'">'; 
        $result .= '<option value="">'.$show_option_none_text.'</option>';

        if ( ! empty( $options ) ) { 

            if ( !array_key_exists ( $attribute, $this->attributelist ) ) 
                $this->attributelist[$attribute] = $options;
            
            if ( $product && taxonomy_exists( $attribute ) ) {
                $terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
                foreach ( $terms as $term ) { 
                    if ( in_array( $term->slug, $options, true ) ) { 
                        $result .= '<option value="'.$term->slug.'" '.selected( sanitize_title( $args['selected'] ), $term->slug, false ).'>'.esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ).'</option>';
                    }
                }
            } else {
                foreach ( $options as $option ) {
                    $selected_item = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                    $result .= '<option value="'.$option.'" '.$selected_item.'>'.esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ).'</option>';
                }
            }
        } 

        $result .= '</select>'; 

		return $result;

	}

    public function checkType ( $attr, $prod ) {

        $attributes = wc_get_attribute_taxonomies();
        if ( $attributes && $attr ) {
            foreach ( $attributes as $attribute ) { 
                if ( $attr['attribute'] == 'pa_'.$attribute->attribute_name ) {
                    return $attribute->attribute_type;
                    break;
                }
            }
        }
		return '';

	}

    public function acovswAttrTypeLabel ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $labelSettings, $attributeSettings, $customType ) 
    {

        $result = '<div class="acovswHideData acovswTriggerSelect">'.$view.'</div>';
        if ( $args ) {

            $options        = $args['options'];
            $attribute      = $args['attribute'];
            $attributeName  = $attribute;
            $selected       = $args['selected'];
            // $attrIndex = array_search ( str_replace ( 'pa_', '', $attribute ), array_column ( $attributeSettings, 'attr_name' ) );
            if ( $customType ) {

                $attrStyle          = array_key_exists ( 'attr_style', $attributeSettings ) ? $attributeSettings['attr_style'] : '';
                if ( $options ) {
                    $zindex = 100; 
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-custom-style" data-attribute="'.$attributeName.'">';
                    foreach ( $options as $option ) {
                        $zindex = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if  ( $tooltipEnabled ) {
                            $toolTipCont = '<span class="acovswToolTip">'.$option.'</span>';
                        } else {
                            $toolTipCont = '';
                        }
                        $result .= '<li '.$selected.' data-attr_name='.$option.' data-attr_value='.$option.' style="z-index:'.$zindex.';"><span class="acovsw-variation">'.$toolTipCont.$option.'</span></li>';
                    }
                    $result .= '</ul>';
                }

            } else {

                $attrIndex = $this->acovsw_array_search_multi ( str_replace ( 'pa_', '', $attribute ), $attributeSettings ); 
                $attrStyle = $attributeSettings[$attrIndex]['attr_style'] ? $attributeSettings[$attrIndex]['attr_style'] : 'horizontal';
                $taxonomy = wc_get_product_terms ( $product->get_id(), $attribute ); // Get taxonomy items
                if ( $taxonomy ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-button-style" data-attribute="'.$attributeName.'">';
                    $zindex = 100;
                    foreach ( $taxonomy as $tax ) {
                        $tax_slug   = $tax->slug;
                        $zindex     = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if ( in_array ( $tax_slug, $options ) ) {
                            if  ( $tooltipEnabled ) {
                                $toolTipCont = '<span class="acovswToolTip">'.$tax->name.'</span>';
                            } else {
                                $toolTipCont = '';
                            }
                            $result .= '<li '.$selected.' data-attr_name='.$tax->name.' data-attr_value='.$tax->slug.' style="z-index:'.$zindex.';"><span class="acovsw-variation">'.$toolTipCont.$tax->name.'</span></li>';
                        }
                    }
                    $result .= '</ul>';
                }

            }
        }
        return $result;

    }

    public function acovswAttrTypeRadio ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $attributeSettings, $customType ) 
    {

        $result = '<div class="acovswHideData acovswTriggerSelect">'.$view.'</div>';
        if ( $args ) { 
            $options        = $args['options'];
            $attribute      = $args['attribute'];
            $attributeName  = $attribute;
            // $attrIndex = array_search ( str_replace ( 'pa_', '', $attribute ), array_column ( $attributeSettings, 'attr_name' ) );
            $selected       = $args['selected'];
            
            if ( $customType ) { 

                $attrStyle          = array_key_exists ( 'attr_style', $attributeSettings ) ? $attributeSettings['attr_style'] : '';
                if ( $options ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-radio-style" data-attribute="'.$attributeName.'">';
                    $zindex  = 100;
                    foreach ( $options as $option ) {
                        $zindex = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if  ( $tooltipEnabled ) {
                            $toolTipCont = '<span class="acovswToolTip">'.$option.'</span>';
                        } else {
                            $toolTipCont = '';
                        }
                        $result .= '<li '.$selected.' data-attr_name='.$option.' data-attr_value='.$option.' class="acovsw-attribute-single-option" style="z-index:'.$zindex.';"><span class="acovsw-variation">';
                        $result .= $toolTipCont;
                        $result .= '<input type="radio" id="'.$option.'" name="'.$attribute.'" value="'.$option.'" '.$selected.'>';
                        $result .= '<label>'.$option.'</label>';
                        $result .= '<div class="acovswRadioTicked"></div>';
                        $result .= '</span></li>';
                    }
                    $result .= '</ul>';
                } 

            } else {

                $attrIndex = $this->acovsw_array_search_multi ( str_replace ( 'pa_', '', $attribute ), $attributeSettings ); 
                $attrStyle = $attributeSettings[$attrIndex]['attr_style'] ? $attributeSettings[$attrIndex]['attr_style'] : 'horizontal';
                $taxonomy = wc_get_product_terms ( $product->get_id(), $attribute ); // Get taxonomy items
                if ( $taxonomy ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-radio-style" data-attribute="'.$attributeName.'">';
                    $zindex  = 100;
                    foreach ( $taxonomy as $tax ) {
                        $tax_slug   = $tax->slug;
                        $zindex     = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if  ( $tooltipEnabled ) {
                            $toolTipCont = '<span class="acovswToolTip">'.$tax->name.'</span>';
                        } else {
                            $toolTipCont = '';
                        }
                        if ( in_array ( $tax_slug, $options ) ) {
                            $result .= '<li '.$selected.' data-attr_name='.$tax->name.' data-attr_value='.$tax->slug.' class="acovsw-attribute-single-option" style="z-index:'.$zindex.';"><span class="acovsw-variation">';
                            $result .= $toolTipCont;
                            $result .= '<input type="radio" id="'.$tax_slug.'" name="'.$attribute.'" value="'.$tax_slug.'" '.$selected.'>';
                            $result .= '<label>'.$tax->name.'</label>';
                            $result .= '<div class="acovswRadioTicked"></div>';
                            $result .= '</span></li>';
                        }
                    }
                    $result .= '</ul>';
                } 

            }
        }
        return $result;

    }

    public function acovswAttrTypeColor ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $attributeSettings, $customType ) 
    {

        $result = '<div class="acovswHideData acovswTriggerSelect">'.$view.'</div>';
        if ( $args ) {
            $attribute      = $args['attribute'];
            $attributeName  = $attribute;
            // $attrIndex = array_search ( str_replace ( 'pa_', '', $attribute ), array_column ( $attributeSettings, 'attr_name' ) );
            $options        = $args['options'];
            $selected       = $args['selected'];

            if ( $customType ) { 

                $attrStyle      = array_key_exists ( 'attr_style', $attributeSettings ) ? $attributeSettings['attr_style'] : '';
                $attrData       = array_key_exists ( 'attr_color', $attributeSettings ) ? $attributeSettings['attr_color'] : [];
                if ( $options ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-color-style" data-attribute="'.$attributeName.'">';
                    $zindex  = 100;
                    foreach ( $options as $option ) {
                        $colorIndex = array_search ( strtolower($option), array_column ( $attrData, 'termslug' ) );
                        $color      = ( $colorIndex >= 0 && array_key_exists ( $colorIndex, $attrData ) ) ? "background:rgba(".$attrData[$colorIndex]['color']['r'].", ".$attrData[$colorIndex]['color']['g'].", ".$attrData[$colorIndex]['color']['b'].", ".$attrData[$colorIndex]['color']['a'].");" : "background:".$option.";";
                        $tax_slug   = $option;
                        $zindex     = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if ( in_array ( $tax_slug, $options ) ) {
                            if  ( $tooltipEnabled ) {
                                $toolTipCont = '<span class="acovswToolTip">'.$option.'</span>';
                            } else {
                                $toolTipCont = '';
                            }
                            $result .= '<li '.$selected.' data-attr_name='.$option.' data-attr_value='.$option.' style="z-index:'.$zindex.';"><span class="acovsw-variation" style="'.$color.'">'.$toolTipCont.'</span></li>';
                        }
                    }
                    $result .= '</ul>';
                }

            } else {

                $attrIndex  = $this->acovsw_array_search_multi ( str_replace ( 'pa_', '', $attribute ), $attributeSettings ); 
                $attrData   = ( $attrIndex >= 0 ) ? $attributeSettings[$attrIndex]['attr_color'] : [];
                $attrStyle  = $attributeSettings[$attrIndex]['attr_style'] ? $attributeSettings[$attrIndex]['attr_style'] : 'horizontal';
                $taxonomy   = wc_get_product_terms ( $product->get_id(), $attribute ); // Get taxonomy items
                if ( $taxonomy ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-color-style" data-attribute="'.$attributeName.'">';
                    $zindex  = 100;
                    foreach ( $taxonomy as $tax ) {
                        $colorIndex = array_search ( $tax->slug, array_column ( $attrData, 'termslug' ) );
                        $color      = ( $colorIndex >= 0  && array_key_exists ( $colorIndex, $attrData ) ) ? "background:rgba(".$attrData[$colorIndex]['color']['r'].", ".$attrData[$colorIndex]['color']['g'].", ".$attrData[$colorIndex]['color']['b'].", ".$attrData[$colorIndex]['color']['a'].");" : "background:".$tax->slug.";";
                        $tax_slug   = $tax->slug;
                        $zindex     = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if ( in_array ( $tax_slug, $options ) ) {
                            if  ( $tooltipEnabled ) {
                                $toolTipCont = '<span class="acovswToolTip">'.$tax->name.'</span>';
                            } else {
                                $toolTipCont = '';
                            }
                            $result .= '<li '.$selected.' data-attr_name='.$tax->name.' data-attr_value='.$tax->slug.' style="z-index:'.$zindex.';"><span class="acovsw-variation" style="'.$color.'">'.$toolTipCont.'</span></li>';
                        }
                    }
                    $result .= '</ul>';
                } 
                
            }
        }
        return $result;

    }

    public function acovswAttrTypeImage ( $view, $args, $product, $CSSClass, $tooltipEnabled, $tooltipPosition, $attributeSettings, $customType ) 
    {

        $result = '<div class="acovswHideData acovswTriggerSelect">'.$view.'</div>';
        if ( $args ) {
            $attribute      = $args['attribute'];
            $attributeName  = $attribute;
            // $attrIndex = array_search ( str_replace ( 'pa_', '', $attribute ), array_column ( $attributeSettings, 'attr_name' ) );
            $options        = $args['options'];
            $selected       = $args['selected'];

            if ( $customType ) {  

                $attrStyle      = array_key_exists ( 'attr_style', $attributeSettings ) ? $attributeSettings['attr_style'] : '';
                $attrData       = array_key_exists ( 'attr_image', $attributeSettings ) ? $attributeSettings['attr_image'] : [];
                $attrHeight     = array_key_exists ( 'attr_iconHeight', $attributeSettings ) ? $attributeSettings['attr_iconHeight'] : 50;
                $attrWidth      = array_key_exists ( 'attr_iconWidth', $attributeSettings ) ? $attributeSettings['attr_iconWidth'] : 50;
                $style          = 'width:'.$attrWidth.'px;height:'.$attrHeight.'px;';

                if ( $options ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-image-style" data-attribute="'.$attributeName.'">';
                    $zindex  = 100;
                    foreach ( $options as $option ) { ;
                        $imgIndex   = array_key_exists ( 'termslug', $attrData ) ? array_search ( strtolower($option), array_column ( $attrData, 'termslug' ) ) : '';
                        $img        = ( $imgIndex >= 0  && array_key_exists ( $imgIndex, $attrData ) ) ? $attrData[$imgIndex]['url'] : wc_placeholder_img_src();
                        $tax_slug   = $option;
                        $zindex     = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if ( in_array ( $tax_slug, $options ) ) {
                            if  ( $tooltipEnabled ) {
                                $toolTipCont = '<span class="acovswToolTip">'.$option.'</span>';
                            } else {
                                $toolTipCont = '';
                            }
                            $result .= '<li '.$selected.' data-attr_name='.$option.' data-attr_value='.$option.' style="z-index:'.$zindex.';"><span class="acovsw-variation" style="'.$style.'">'.$toolTipCont;
                            $result .='<img src="'.$img.'" alt="'.$option.'" />';
                            $result .='</span></li>';
                        }
                    }
                    $result .= '</ul>';
                } 

            } else {

                $attrIndex  = $this->acovsw_array_search_multi ( str_replace ( 'pa_', '', $attribute ), $attributeSettings ); 
                $attrData   = ( $attrIndex >= 0 ) ? $attributeSettings[$attrIndex]['attr_image'] : [];
                $attrHeight = ( $attrIndex >= 0 ) ? $attributeSettings[$attrIndex]['attr_iconHeight'] : 50;
                $attrWidth  = ( $attrIndex >= 0 ) ? $attributeSettings[$attrIndex]['attr_iconWidth'] : 50;
                $attrStyle  = $attributeSettings[$attrIndex]['attr_style'] ? $attributeSettings[$attrIndex]['attr_style'] : 'horizontal';
                $taxonomy   = wc_get_product_terms ( $product->get_id(), $attribute ); // Get taxonomy items
                $style      = 'width:'.$attrWidth.'px;height:'.$attrHeight.'px;';
                if ( $taxonomy ) {
                    $result .= '<ul class="'.$CSSClass.' acovsw-style-'.$attrStyle.' acovsw-image-style" data-attribute="'.$attributeName.'">';
                    $zindex  = 100;
                    foreach ( $taxonomy as $tax ) {
                        $imgIndex   = ( array_search ( $tax->slug, array_column ( $attrData, 'termslug' ) ) >= 0 ) ? array_search ( $tax->slug, array_column ( $attrData, 'termslug' ) ) : -1; 
                        $img        = ( $imgIndex >= 0 && array_key_exists ( $imgIndex, $attrData ) ) ? $attrData[$imgIndex]['url'] : wc_placeholder_img_src();
                        $tax_slug   = $tax->slug;
                        $zindex     = ( $tooltipPosition == 'top' || $tooltipPosition == 'right' ) ? $zindex - 1 : $zindex + 1;
                        if ( in_array ( $tax_slug, $options ) ) {
                            if  ( $tooltipEnabled ) {
                                $toolTipCont = '<span class="acovswToolTip">'.$tax->name.'</span>';
                            } else {
                                $toolTipCont = '';
                            }
                            $result .= '<li '.$selected.' data-attr_name='.$tax->name.' data-attr_value='.$tax->slug.' style="z-index:'.$zindex.';"><span class="acovsw-variation" style="'.$style.'">'.$toolTipCont;
                            $result .='<img src="'.$img.'" alt="'.$tax->name.'" />';
                            $result .='</span></li>';
                        }
                    }
                    $result .= '</ul>';
                } 

            }
        }
        return $result;

    }

    public function acovswCustomStyles ( $generalSettings, $labelSettings, $radioSettings, $colorSettings, $imageSettings, $tooltipOptions, $tooltipEnabled ) {

        // Tooltip Styles
        if ( $tooltipEnabled && !empty ( $tooltipOptions ) ) { 

            $toolTipBG          = array_key_exists ( 'toolTipBGColor', $tooltipOptions ) ? $tooltipOptions['toolTipBGColor'] : '';
            $toolTipBorder      = array_key_exists ( 'toolTipBorderColor', $tooltipOptions ) ? $tooltipOptions['toolTipBorderColor'] : '';
            $toolTipText        = array_key_exists ( 'toolTipTextColor', $tooltipOptions ) ? $tooltipOptions['toolTipTextColor'] : '';
            $toolTipFontSize    = array_key_exists ( 'tooltipFontSize', $tooltipOptions ) ? $tooltipOptions['tooltipFontSize'] : 12;
            $toolTipBorderRaius = array_key_exists ( 'tooltipBorderRadius', $tooltipOptions ) ? $tooltipOptions['tooltipBorderRadius'] : 0;
            $tooltipPosition    = array_key_exists ( 'tooltipPosition', $tooltipOptions ) ? $tooltipOptions['tooltipPosition'] : '';
            $tooltipPositionCSS = ( $tooltipPosition == 'bottom' ) ? 'top: 125%; bottom: auto;' : ( ( $tooltipPosition == 'right' ) ? 'top: 50%; bottom: auto; left: 105%; transform: translateX(0%); transform: translateY(-50%);' : ( ( $tooltipPosition == 'left' ) ? 'top: 50%; bottom: auto; left: auto; right: 105%; transform: translateX(0%); transform: translateY(-50%);' : '' ) );

            $background = ( $toolTipBG ) ? "background-color: rgba(".$toolTipBG['r'].", ".$toolTipBG['g'].", ".$toolTipBG['b'].", ".$toolTipBG['a'].");" : ""; 
            $border     = ( $toolTipBorder ) ? "border: 1px solid rgba(".$toolTipBorder['r'].", ".$toolTipBorder['g'].", ".$toolTipBorder['b'].", ".$toolTipBorder['a'].");" : ""; 
            $color      = ( $toolTipText ) ? "color: rgba(".$toolTipText['r'].", ".$toolTipText['g'].", ".$toolTipText['b'].", ".$toolTipText['a'].");" : ""; 

            $this->customStyles .= "
                .acovswToolTip { 
                    ".$background.$color.$border.$tooltipPositionCSS."
                    border-radius: ".$toolTipBorderRaius."px; 
                    font-size: ".$toolTipFontSize."px; 
                }";

        }

        if ( !empty ( $generalSettings ) ) {
            $fontSize = array_key_exists ( 'attributeFontSize', $generalSettings ) ? $generalSettings['attributeFontSize'] : 12;

            $this->customStyles .= "
                .acovsw-attribute-style li { 
                    font-size: ".$fontSize."px; 
                }";
        }

        if ( !empty ( $labelSettings ) ) {

            $labelHeight            = array_key_exists ( 'labelHeight', $labelSettings ) ? $labelSettings['labelHeight'] : 30;
            $labelLineHeight        = array_key_exists ( 'labelLineHeight', $labelSettings ) ? $labelSettings['labelLineHeight'] : 30;
            $labelType              = array_key_exists ( 'labelType', $labelSettings ) ? $labelSettings['labelType'] : '';
            $labelBorderRadius      = array_key_exists ( 'labelBorderRadius', $labelSettings ) ? ( ( $labelType == 'rounded' ) ? $labelSettings['labelBorderRadius'].'px' : ( $labelType == 'circle' ? '50%' : '0px' ) ) : '';
            $labelFontSize          = array_key_exists ( 'labelFontSize', $labelSettings ) ? $labelSettings['labelFontSize'] : 12;
            $labeltextColor         = array_key_exists ( 'labeltextColor', $labelSettings ) ? $labelSettings['labeltextColor'] : '';
            $buttonBGColor          = array_key_exists ( 'buttonBGColor', $labelSettings ) ? $labelSettings['buttonBGColor'] : '';
            $borderColor            = array_key_exists ( 'borderColor', $labelSettings ) ? $labelSettings['borderColor'] : '';
            $labeltextHoverColor    = array_key_exists ( 'labeltextHoverColor', $labelSettings ) ? $labelSettings['labeltextHoverColor'] : '';
            $buttonBGHoverColor     = array_key_exists ( 'buttonBGHoverColor', $labelSettings ) ? $labelSettings['buttonBGHoverColor'] : '';
            $borderHoverColor       = array_key_exists ( 'borderHoverColor', $labelSettings ) ? $labelSettings['borderHoverColor'] : '';
            $labeltextSelectedColor = array_key_exists ( 'labeltextSelectedColor', $labelSettings ) ? $labelSettings['labeltextSelectedColor'] : '';
            $buttonBGSelectedColor  = array_key_exists ( 'buttonBGSelectedColor', $labelSettings ) ? $labelSettings['buttonBGSelectedColor'] : '';
            $borderSelectedColor    = array_key_exists ( 'borderSelectedColor', $labelSettings ) ? $labelSettings['borderSelectedColor'] : '';

            $labelCSS  = ( $buttonBGColor ) ? "background-color: rgba(".$buttonBGColor['r'].", ".$buttonBGColor['g'].", ".$buttonBGColor['b'].", ".$buttonBGColor['a'].");" : ""; 
            $labelCSS .= ( $labeltextColor ) ? "color: rgba(".$labeltextColor['r'].", ".$labeltextColor['g'].", ".$labeltextColor['b'].", ".$labeltextColor['a'].");" : ""; 
            $labelCSS .= ( $borderColor ) ? "border: 1px solid rgba(".$borderColor['r'].", ".$borderColor['g'].", ".$borderColor['b'].", ".$borderColor['a'].");" : ""; 
            $labelCSS .= "border-radius: ".$labelBorderRadius."; font-size: ".$labelFontSize."px; line-height: ".$labelLineHeight."px; height: ".$labelHeight."px;";

            $hoverCSS  = ( $labeltextHoverColor ) ? "color: rgba(".$labeltextHoverColor['r'].", ".$labeltextHoverColor['g'].", ".$labeltextHoverColor['b'].", ".$labeltextHoverColor['a'].");" : ""; 
            $hoverCSS .= ( $buttonBGHoverColor ) ? "background-color: rgba(".$buttonBGHoverColor['r'].", ".$buttonBGHoverColor['g'].", ".$buttonBGHoverColor['b'].", ".$buttonBGHoverColor['a'].");" : ""; 
            $hoverCSS .= ( $borderHoverColor ) ? "border: 1px solid rgba(".$borderHoverColor['r'].", ".$borderHoverColor['g'].", ".$borderHoverColor['b'].", ".$borderHoverColor['a'].");" : ""; 

            $selectedCSS  = ( $labeltextSelectedColor ) ? "color: rgba(".$labeltextSelectedColor['r'].", ".$labeltextSelectedColor['g'].", ".$labeltextSelectedColor['b'].", ".$labeltextSelectedColor['a'].");" : ""; 
            $selectedCSS .= ( $buttonBGSelectedColor ) ? "background-color: rgba(".$buttonBGSelectedColor['r'].", ".$buttonBGSelectedColor['g'].", ".$buttonBGSelectedColor['b'].", ".$buttonBGSelectedColor['a'].");" : ""; 
            $selectedCSS .= ( $borderSelectedColor ) ? "border: 1px solid rgba(".$borderSelectedColor['r'].", ".$borderSelectedColor['g'].", ".$borderSelectedColor['b'].", ".$borderSelectedColor['a'].");" : ""; 

            $this->customStyles .= "
                ul.acovsw-button-style li .acovsw-variation {
                    ".$labelCSS."
                }
                ul.acovsw-button-style li:hover .acovsw-variation {
                    ".$hoverCSS."
                }
                ul.acovsw-button-style li.acovsw-selected .acovsw-variation {
                    ".$selectedCSS."
                }";
        }

        if ( !empty ( $radioSettings ) ) {

            $radiotextColor             = array_key_exists ( 'radiotextColor', $radioSettings ) ? $radioSettings['radiotextColor'] : '';
            $radioborderColor           = array_key_exists ( 'radioborderColor', $radioSettings ) ? $radioSettings['radioborderColor'] : '';
            $radiotextHoverColor        = array_key_exists ( 'radiotextHoverColor', $radioSettings ) ? $radioSettings['radiotextHoverColor'] : '';
            $radioHoverOpacity          = ( array_key_exists ( 'radioHoverOpacity', $radioSettings ) && $radioSettings['radioHoverOpacity'] != '' ) ? (int)$radioSettings['radioHoverOpacity'] / 100 : 0.9;
            $radioborderHoverColor      = array_key_exists ( 'radioborderHoverColor', $radioSettings ) ? $radioSettings['radioborderHoverColor'] : '';
            $radiotextSelectedColor     = array_key_exists ( 'radiotextSelectedColor', $radioSettings ) ? $radioSettings['radiotextSelectedColor'] : '';
            $radioSelectedOpacity       = ( array_key_exists ( 'radioSelectedOpacity', $radioSettings ) && $radioSettings['radioSelectedOpacity'] != '' ) ? (int)$radioSettings['radioSelectedOpacity'] / 100 : 0.75; 
            $radioborderSelectedColor   = array_key_exists ( 'radioborderSelectedColor', $radioSettings ) ? $radioSettings['radioborderSelectedColor'] : '';

            $radioCSS       = ( $radiotextColor ) ? "color: rgba(".$radiotextColor['r'].", ".$radiotextColor['g'].", ".$radiotextColor['b'].", ".$radiotextColor['a'].");" : ""; 
            $radioBorder    = ( $radioborderColor ) ? "border: 3px solid rgba(".$radioborderColor['r'].", ".$radioborderColor['g'].", ".$radioborderColor['b'].", ".$radioborderColor['a'].");" : ""; 

            $hoverCSS       = ( $radiotextHoverColor ) ? "color: rgba(".$radiotextHoverColor['r'].", ".$radiotextHoverColor['g'].", ".$radiotextHoverColor['b'].", ".$radiotextHoverColor['a'].");" : ""; 
            $hoverCSS      .= "opacity: ".$radioHoverOpacity.";"; 
            $hoverBorder    = ( $radioborderHoverColor ) ? "border: 3px solid rgba(".$radioborderHoverColor['r'].", ".$radioborderHoverColor['g'].", ".$radioborderHoverColor['b'].", ".$radioborderHoverColor['a'].");" : ""; 

            $selectedCSS    = ( $radiotextSelectedColor ) ? "color: rgba(".$radiotextSelectedColor['r'].", ".$radiotextSelectedColor['g'].", ".$radiotextSelectedColor['b'].", ".$radiotextSelectedColor['a'].");" : ""; 
            $selectedCSS   .= "opacity: ".$radioSelectedOpacity.";"; 
            $selectedBorder = ( $radioborderSelectedColor ) ? "border: 3px solid rgba(".$radioborderSelectedColor['r'].", ".$radioborderSelectedColor['g'].", ".$radioborderSelectedColor['b'].", ".$radioborderSelectedColor['a'].");" : ""; 
            $selectedBackground = ( $radioborderSelectedColor ) ? "background: rgba(".$radioborderSelectedColor['r'].", ".$radioborderSelectedColor['g'].", ".$radioborderSelectedColor['b'].", ".$radioborderSelectedColor['a'].");" : ""; 

            $this->customStyles .= "
                .acovsw-radio-style .acovsw-attribute-single-option .acovsw-variation {
                    ".$radioCSS."
                }
                .acovsw-radio-style .acovsw-attribute-single-option .acovsw-variation .acovswRadioTicked {
                    ".$radioBorder."
                }
                .acovsw-radio-style .acovsw-attribute-single-option:hover .acovsw-variation .acovswRadioTicked {
                    ".$hoverBorder."
                }
                .acovsw-radio-style .acovsw-attribute-single-option:hover .acovsw-variation {
                    ".$hoverCSS."
                }
                .acovsw-radio-style .acovsw-attribute-single-option .acovsw-variation input[type=radio]:checked ~ .acovswRadioTicked,
                .acovsw-radio-style .acovsw-attribute-single-option.acovsw-selected .acovsw-variation .acovswRadioTicked {
                    ".$selectedBorder."
                }
                .acovsw-radio-style .acovsw-attribute-single-option .acovsw-variation input[type=radio]:checked ~ .acovswRadioTicked::before, 
                .acovsw-radio-style .acovsw-attribute-single-option.acovsw-selected .acovsw-variation .acovswRadioTicked::before{
                    ".$selectedBackground."
                }
                .acovsw-radio-style .acovsw-attribute-single-option .acovsw-variation input[type=radio]:checked ~ label,
                .acovsw-radio-style .acovsw-attribute-single-option.acovsw-selected .acovsw-variation label {
                    ".$selectedCSS."
                }";
        }

        if ( !empty ( $colorSettings ) ) {

            $colorHeight                = array_key_exists ( 'colorHeight', $colorSettings ) ? $colorSettings['colorHeight'] : 30;
            $colorWidth                 = array_key_exists ( 'colorWidth', $colorSettings ) ? $colorSettings['colorWidth'] : 30;
            $colorType                  = array_key_exists ( 'colorType', $colorSettings ) ? $colorSettings['colorType'] : '';
            $colorBorderRadius          = array_key_exists ( 'colorBorderRadius', $colorSettings ) ? ( ( $colorType == 'rounded' ) ? $colorSettings['colorBorderRadius'].'px' : ( $colorType == 'circle' ? '50%' : '0px' ) ) : '0px';
            $colorBorderColor           = array_key_exists ( 'colorBorderColor', $colorSettings ) ? $colorSettings['colorBorderColor'] : '';
            $colorborderHoverColor      = array_key_exists ( 'colorborderHoverColor', $colorSettings ) ? $colorSettings['colorborderHoverColor'] : '';
            $colorHoverOpacity          = ( array_key_exists ( 'colorHoverOpacity', $colorSettings ) && $colorSettings['colorHoverOpacity'] != '' ) ? (int)$colorSettings['colorHoverOpacity'] / 100 : 0.9;
            $colorSelectedOpacity       = ( array_key_exists ( 'colorSelectedOpacity', $colorSettings ) && $colorSettings['colorSelectedOpacity'] != '' )  ? (int)$colorSettings['colorSelectedOpacity'] / 100 : 0.75;
            $colorborderSelectedColor   = array_key_exists ( 'colorborderSelectedColor', $colorSettings ) ? $colorSettings['colorborderSelectedColor'] : '';

            $colorCSS   = ( $colorBorderColor ) ? "border: 1px solid rgba(".$colorBorderColor['r'].", ".$colorBorderColor['g'].", ".$colorBorderColor['b'].", ".$colorBorderColor['a'].");" : ""; 
            $colorCSS  .= "border-radius: ".$colorBorderRadius."; width: ".$colorWidth."px; height: ".$colorHeight."px;";

            $hoverCSS   = ( $colorborderHoverColor ) ? "border: 1px solid rgba(".$colorborderHoverColor['r'].", ".$colorborderHoverColor['g'].", ".$colorborderHoverColor['b'].", ".$colorborderHoverColor['a'].");" : ""; 
            $hoverCSS  .= "opacity: ".$colorHoverOpacity.";";

            $selectedCSS  = ( $colorborderSelectedColor ) ? "border: 1px solid rgba(".$colorborderSelectedColor['r'].", ".$colorborderSelectedColor['g'].", ".$colorborderSelectedColor['b'].", ".$colorborderSelectedColor['a'].");" : "";
            $selectedCSS .= "opacity: ".$colorSelectedOpacity.";"; 

            $this->customStyles .= "
                ul.acovsw-color-style li .acovsw-variation {
                    ".$colorCSS."
                }
                ul.acovsw-color-style li:hover .acovsw-variation {
                    ".$hoverCSS."
                }
                ul.acovsw-color-style li.acovsw-selected .acovsw-variation {
                    ".$selectedCSS."
                }";
        }

        if ( !empty ( $imageSettings ) ) {

            $imageHeight                = array_key_exists ( 'imageHeight', $imageSettings ) ? $imageSettings['imageHeight'] : 50;
            $imageWidth                 = array_key_exists ( 'imageWidth', $imageSettings ) ? $imageSettings['imageWidth'] : 50;
            $imageType                  = array_key_exists ( 'imageType', $imageSettings ) ? $imageSettings['imageType'] : '';
            $imageBorderRadius          = array_key_exists ( 'imageBorderRadius', $imageSettings ) ? ( ( $imageType == 'rounded' ) ? $imageSettings['imageBorderRadius'].'px' : ( $imageType == 'circle' ? '50%' : '0px' ) ) : '0px';
            $imageBorderColor           = array_key_exists ( 'imageBorderColor', $imageSettings ) ? $imageSettings['imageBorderColor'] : '';
            $imageBorderHoverColor      = array_key_exists ( 'imageBorderHoverColor', $imageSettings ) ? $imageSettings['imageBorderHoverColor'] : '';
            $imageHoverOpacity          = ( array_key_exists ( 'imageHoverOpacity', $imageSettings ) && $imageSettings['imageHoverOpacity'] != '' ) ? (int)$imageSettings['imageHoverOpacity'] / 100 : 0.9;
            $imageBorderSelectedColor   = array_key_exists ( 'imageBorderSelectedColor', $imageSettings ) ? $imageSettings['imageBorderSelectedColor'] : '';
            $imageSelectedOpacity       = ( array_key_exists ( 'imageSelectedOpacity', $imageSettings ) && $imageSettings['imageSelectedOpacity'] != '' ) ? (int)$imageSettings['imageSelectedOpacity'] / 100 : 0.75;

            $imageCSS = ( $imageBorderColor ) ? "border: 1px solid rgba(".$imageBorderColor['r'].", ".$imageBorderColor['g'].", ".$imageBorderColor['b'].", ".$imageBorderColor['a'].");" : ""; 
            $imageCSS .= "border-radius: ".$imageBorderRadius."; width: ".$imageWidth."px; height: ".$imageHeight."px;";

            $hoverCSS = ( $imageBorderHoverColor ) ? "border: 1px solid rgba(".$imageBorderHoverColor['r'].", ".$imageBorderHoverColor['g'].", ".$imageBorderHoverColor['b'].", ".$imageBorderHoverColor['a'].");" : ""; 
            $hoverCSS .= "opacity: ".$imageHoverOpacity.";";

            $selectedCSS = ( $imageBorderSelectedColor ) ? "border: 1px solid rgba(".$imageBorderSelectedColor['r'].", ".$imageBorderSelectedColor['g'].", ".$imageBorderSelectedColor['b'].", ".$imageBorderSelectedColor['a'].");" : ""; 
            $selectedCSS .= "opacity: ".$imageSelectedOpacity.";";

            $this->customStyles .= "
                ul.acovsw-image-style li .acovsw-variation {
                    ".$imageCSS."
                }
                ul.acovsw-image-style li:hover .acovsw-variation {
                    ".$hoverCSS."
                }
                ul.acovsw-image-style li.acovsw-selected .acovsw-variation {
                    ".$selectedCSS."
                }";
        }

    }

    public function customStyles()
    {

        $styles = $this->customStyles;
        $result = '';
        if ( $styles ) {
            $result = '<style>'.$styles.'</style>';
        }
        echo $result;

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
     * Cloning is forbidden.
     * @since 1.0.0
    **/
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /** 
     * Unserializing instances of this class is forbidden.
     * @since 1.0.0
    **/
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

}
