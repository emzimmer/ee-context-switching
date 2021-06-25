<?php defined('ABSPATH')||exit('WP absolute path is not defined.');global $EE_ContextSwitching;define('EECS_PLUGIN_DIR',$EE_ContextSwitching->get_plugin_root().'plugin/');define('EECS_PLUGIN_URL',$EE_ContextSwitching->get_plugin_root_url().'plugin/');define('EECS_DIST',EECS_PLUGIN_DIR.'dist/');define('EECS_DIST_URL',EECS_PLUGIN_URL.'dist/');function get_context_switching_data(){$templates=['templates'=>[],'reusable_parts'=>[],'pages'=>[]];$context_switching_query=get_posts(['post_type'=>'ct_template','order'=>'ASC','orderby'=>'title','numberposts'=>-1]);if(!empty($context_switching_query)){foreach($context_switching_query as $index=>$template){$template_type=get_post_meta($template->ID,'ct_template_type',true)=='reusable_part'?'reusable_parts':'templates';$ct_parent_template=get_post_meta($template->ID,'ct_parent_template',true);$shortcodes='';if($ct_parent_template&&$ct_parent_template>0){$shortcodes=get_post_meta($ct_parent_template,'ct_builder_shortcodes',true);}$isInner=$shortcodes&&strpos($shortcodes,'[ct_inner_content');$template_data=['title'=>$template->post_title,'id'=>$template->ID,'nonce'=>wp_create_nonce('oxygen-nonce-'.$template->ID),'isInner'=>$isInner];$templates[$template_type][$template->post_name]=$template_data;}wp_reset_postdata();}$context_switching_query=get_posts(['post_type'=>'page','order'=>'ASC','orderby'=>'title','numberposts'=>-1]);if(!empty($context_switching_query)){foreach($context_switching_query as $index=>$page){$page_other_template_id=get_post_meta($page->ID,'ct_other_template',true);$page_uses_other_template=$page_other_template_id>0;$isInner=$page_uses_other_template;if($page_other_template_id){$isInner=get_post_status($page_other_template_id)=='publish';}$page_data=['title'=>$page->post_title,'id'=>$page->ID,'nonce'=>wp_create_nonce('oxygen-nonce-'.$page->ID),'isInner'=>$isInner];$templates['pages'][$page->post_name]=$page_data;}wp_reset_postdata();}$templates=!empty($templates)?$templates:false;return $templates;}add_action('wp_ajax_ee_refresh_contexts','ee_refresh_contexts');function ee_refresh_contexts(){$templates=['templates'=>[],'reusable_parts'=>[],'pages'=>[]];$csq=get_posts(['post_type'=>'ct_template','order'=>'ASC','orderby'=>'title','numberposts'=>-1]);if(!empty($csq)):foreach($csq as $index=>$template):$template_type=get_post_meta($template->ID,'ct_template_type',true)=='reusable_part'?'reusable_parts':'templates';$ct_parent_template=get_post_meta($template->ID,'ct_parent_template',true);$shortcodes='';if($ct_parent_template&&$ct_parent_template>0)$shortcodes=get_post_meta($ct_parent_template,'ct_builder_shortcodes',true);$isInner=$shortcodes&&strpos($shortcodes,'[ct_inner_content');$template_data=['title'=>$template->post_title,'id'=>$template->ID,'nonce'=>wp_create_nonce('oxygen-nonce-'.$template->ID),'isInner'=>$isInner];$templates[$template_type][$template->post_name]=$template_data;endforeach;wp_reset_postdata();endif;$csq=get_posts(['post_type'=>'page','order'=>'ASC','orderby'=>'title','numberposts'=>-1]);if(!empty($csq)):foreach($csq as $index=>$page):$page_other_template_id=get_post_meta($page->ID,'ct_other_template',true);$page_uses_other_template=$page_other_template_id>0;$isInner=$page_uses_other_template;if($page_other_template_id):$isInner=get_post_status($page_other_template_id)=='publish';endif;$page_data=['title'=>$page->post_title,'id'=>$page->ID,'nonce'=>wp_create_nonce('oxygen-nonce-'.$page->ID),'isInner'=>$isInner];$templates['pages'][$page->post_name]=$page_data;endforeach;wp_reset_postdata();endif;$templates=!empty($templates)?json_encode($templates):false;echo $templates;wp_die();}function enqueue_context_switching(){global $EE_ContextSwitching,$post;$localize=['home_url'=>get_home_url(),'admin_url'=>admin_url(),'oxy_icons'=>get_home_url().'/wp-content/plugins/oxygen/component-framework/toolbar/UI/oxygen-icons/','ajaxurl'=>admin_url('admin-ajax.php'),'post_id'=>$post->ID,'post_name'=>get_the_title($post->ID),'context_switching'=>true,'context_templates'=>get_context_switching_data()];$url=EECS_DIST_URL.'context-switching.js';$version=$EE_ContextSwitching->get_product_info('version');wp_enqueue_script('context-switching',$url,[],$version,true);wp_localize_script('context-switching','eeContextSwitchingSettings',$localize);}if(isset($_GET['ct_builder'])&&true==$_GET['ct_builder']&&!isset($_GET['oxygen_iframe'])){add_action('wp_enqueue_scripts','enqueue_context_switching');}