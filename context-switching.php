<?php
/**
 * Context Switching
 *
 * @package           Context Switching
 * @author            Max Zimmer
 * @copyright         2021 Max Zimmer
 *
 * @editor-enhancer
 * Plugin Name:       EE Context Switching
 * Plugin URI:        https://editorenhancer.com
 * Description:       Never leave the editor! Switch template contexts and save boat loads of time in Oxygen Builder.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Max Zimmer
 * Author URI:        https://emzimmer.com
 * Text Domain:       ee-context-switching
 */

defined('ABSPATH')||exit('WP absolute path is not defined.');if(!function_exists('EditorEnhancer_Initializer')){if(!isset($EE_ContextSwitching_Product_Arguments)){$EE_ContextSwitching_Product_Arguments=['Website'=>'http://editorenhancer.com','Name'=>'Context Switching','ID'=>12662,'Version'=>'1.0.0'];}if(!isset($EE_ContextSwitching_Admin_Arguments)){$EE_ContextSwitching_Admin_Arguments=['Prefix'=>'eecs','Menu Title'=>'Context Switching','User Capability'=>'manage_options','Top Level Menu'=>false,'Parent Slug'=>'ct_dashboard_page','Menu Position'=>99,'Icon URL'=>'','License on Home'=>true,'Use Tabs'=>false,'Include System Info'=>true];}if(!isset($EE_ContextSwitching_Validation_Arguments)){$EE_ContextSwitching_Validation_Arguments=['Use Remote on Init'=>false,'Check Remote on Shutdown'=>false ];}if(!class_exists('EDD_SL_Quick_Plugin_Starter')){require_once 'licensing/setup.php';}global $EE_ContextSwitching;$EE_ContextSwitching=new EDD_SL_Quick_Plugin_Starter($EE_ContextSwitching_Product_Arguments,$EE_ContextSwitching_Admin_Arguments,$EE_ContextSwitching_Validation_Arguments,__FILE__);function EE_ContextSwitching_Initializer(){global $EE_ContextSwitching;if($EE_ContextSwitching->init()){require_once 'plugin/config.php';}}add_action('plugins_loaded','EE_ContextSwitching_Initializer',999);}