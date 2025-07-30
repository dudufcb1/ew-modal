<?php declare(strict_types = 1);

// odsl-/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php' => 
    array (
      0 => '853836d9afe8a6268e8b7186b983bda1c9aae0c9',
      1 => 
      array (
        0 => 'ewm_submission_cpt',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'register_post_type',
        4 => 'register_meta_fields',
        5 => 'add_meta_boxes',
        6 => 'render_details_meta_box',
        7 => 'render_data_meta_box',
        8 => 'render_meta_box',
        9 => 'get_field_mapping',
        10 => 'save_meta_fields',
        11 => 'add_custom_columns',
        12 => 'custom_column_content',
        13 => 'modify_row_actions',
        14 => 'enqueue_admin_styles',
        15 => 'add_bulk_actions',
        16 => 'handle_bulk_actions',
        17 => 'show_bulk_action_notices',
        18 => 'create_submission',
        19 => 'detect_page_name_from_url',
        20 => 'detect_woocommerce_page',
        21 => 'find_post_by_path',
        22 => 'clear_post_search_cache',
        23 => 'detect_archive_page',
        24 => 'update_existing_submission_titles',
        25 => 'maybe_trigger_title_update',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php' => 
    array (
      0 => 'c2cd0de5a4cf419de51daf788232ad5522d3d6ea',
      1 => 
      array (
        0 => 'ewm_woocommerce',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'is_woocommerce_active',
        4 => 'setup_hooks',
        5 => 'enqueue_wc_scripts',
        6 => 'register_rest_routes',
        7 => 'get_coupons',
        8 => 'get_products',
        9 => 'get_cart_data',
        10 => 'handle_cart_updated',
        11 => 'handle_add_to_cart',
        12 => 'check_cart_abandonment_modals',
        13 => 'check_upsell_modals',
        14 => 'schedule_abandonment_modal',
        15 => 'trigger_upsell_modal',
        16 => 'apply_coupon',
        17 => 'ajax_add_to_cart',
        18 => 'maybe_show_checkout_modal',
        19 => 'add_cart_abandonment_script',
        20 => 'modal_has_wc_integration',
        21 => 'get_modal_wc_config',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php' => 
    array (
      0 => '38b8b5e1c6600fcfc1d8780ed36e883f17265d86',
      1 => 
      array (
        0 => 'ewm_meta_fields',
      ),
      2 => 
      array (
        0 => 'resolve_to_id',
        1 => 'get_instance',
        2 => 'validate_steps_config',
        3 => 'get_supported_field_types',
        4 => 'validate_form_field',
        5 => 'validate_design_config',
        6 => 'validate_trigger_config',
        7 => 'validate_wc_integration',
        8 => 'optimize_page_ids',
        9 => 'validate_display_rules',
        10 => 'validate_field_mapping',
        11 => 'get_meta',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php' => 
    array (
      0 => '09a68d390c18eacfca028a3cca7d6dda3173e66e',
      1 => 
      array (
        0 => 'ewm_shortcodes',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'register_shortcodes',
        4 => 'render_modal_shortcode',
        5 => 'render_trigger_shortcode',
        6 => 'render_stats_shortcode',
        7 => 'validate_modal_id',
        8 => 'can_display_modal',
        9 => 'detect_device',
        10 => 'check_frequency_limit',
        11 => 'get_frequency_expiry',
        12 => 'get_modal_transient_key',
        13 => 'prepare_render_config',
        14 => 'has_modal_shortcode',
        15 => 'get_modal_ids_from_content',
        16 => 'render_debug_shortcode',
        17 => 'get_shortcodes_info',
        18 => 'register_modal_view',
        19 => 'clear_modal_transients',
        20 => 'get_current_modal_config',
        21 => 'get_modal_frequency_config',
        22 => 'check_modal_frequency',
        23 => 'get_transient_expiration',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php' => 
    array (
      0 => '67328727adda2f29d12ae51dd120f1a088b94555',
      1 => 
      array (
        0 => 'ewm_render_core',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'render_modal',
        4 => 'validate_modal',
        5 => 'get_modal_configuration',
        6 => 'apply_default_config',
        7 => 'generate_modal_html',
        8 => 'generate_modal_content',
        9 => 'generate_form_content',
        10 => 'generate_form_fields',
        11 => 'generate_field_input',
        12 => 'generate_announcement_content',
        13 => 'generate_woocommerce_content',
        14 => 'generate_generic_announcement_content',
        15 => 'get_modal_css_classes',
        16 => 'get_modal_data_attributes',
        17 => 'enqueue_modal_assets',
        18 => 'render_modal_scripts',
        19 => 'add_modal_styles',
        20 => 'get_rendered_modals',
        21 => 'get_rendered_modals_info',
        22 => 'ewm_render_modal_core',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php' => 
    array (
      0 => '8c5471b5d0718a63da839942c6f321785925bedf',
      1 => 
      array (
        0 => 'ewm_admin_page',
      ),
      2 => 
      array (
        0 => 'map_special_page_value_to_id',
        1 => 'resolve_to_id',
        2 => 'get_special_page_id',
        3 => '__construct',
        4 => 'get_instance',
        5 => 'init',
        6 => 'add_admin_menu',
        7 => 'enqueue_admin_scripts',
        8 => 'render_modal_builder_page',
        9 => 'render_settings_page',
        10 => 'render_analytics_page',
        11 => 'save_modal_builder',
        12 => 'save_global_settings',
        13 => 'load_modal_builder',
        14 => 'preview_modal',
        15 => 'create_modal',
        16 => 'update_modal',
        17 => 'save_modal_meta',
        18 => 'generate_preview_html',
        19 => 'generate_static_preview',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-capabilities.php' => 
    array (
      0 => '0284ee3f4e061068d89c6f35191d5848213c6804',
      1 => 
      array (
        0 => 'ewm_capabilities',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'setup_capabilities',
        4 => 'add_capabilities_to_roles',
        5 => 'remove_capabilities_from_roles',
        6 => 'map_meta_capabilities',
        7 => 'map_modal_capabilities',
        8 => 'map_submission_capabilities',
        9 => 'filter_user_capabilities',
        10 => 'current_user_can_manage_modals',
        11 => 'current_user_can_view_submissions',
        12 => 'current_user_can_manage_settings',
        13 => 'current_user_can_view_analytics',
        14 => 'current_user_can_edit_modal',
        15 => 'current_user_can_view_submission',
        16 => 'get_plugin_capabilities',
        17 => 'get_role_capabilities',
        18 => 'is_plugin_capability',
        19 => 'add_custom_capability',
        20 => 'remove_custom_capability',
        21 => 'get_capabilities_info',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-modal-cpt.php' => 
    array (
      0 => 'c3b92c72aa430885ca9b156ca4d23137cc4ed6b5',
      1 => 
      array (
        0 => 'ewm_modal_cpt',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'register_post_type',
        4 => 'register_meta_fields',
        5 => 'add_meta_boxes',
        6 => 'render_config_meta_box',
        7 => 'render_shortcode_meta_box',
        8 => 'save_meta_fields',
        9 => 'add_custom_columns',
        10 => 'custom_column_content',
        11 => 'get_modal_config',
        12 => 'save_modal_config',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php' => 
    array (
      0 => '7648fc98daa665254dd238a27f262d3430dce9c4',
      1 => 
      array (
        0 => 'ewm_rest_api',
      ),
      2 => 
      array (
        0 => 'register_test_modal_visibility_route',
        1 => 'test_modal_visibility',
        2 => 'register_user_profile_route',
        3 => 'get_user_profile',
        4 => '__construct',
        5 => 'get_instance',
        6 => 'init',
        7 => 'register_routes',
        8 => 'get_modals',
        9 => 'get_modal',
        10 => 'create_modal',
        11 => 'submit_form',
        12 => 'update_modal',
        13 => 'preview_modal',
        14 => 'generate_preview_html',
        15 => 'get_wc_coupons',
        16 => 'get_active_modals_endpoint',
        17 => 'get_all_published_modals',
        18 => 'apply_modal_filters',
        19 => 'filter_modals_by_page_context',
        20 => 'filter_modals_by_device',
        21 => 'filter_modals_by_user_role',
        22 => 'filter_modals_by_wc_context',
        23 => 'detect_device_type',
        24 => 'is_valid_wc_integration_config',
        25 => 'is_valid_display_rules_config',
        26 => 'is_valid_design_config',
        27 => 'is_valid_triggers_config',
        28 => 'check_admin_permissions',
        29 => 'check_permissions',
        30 => 'prepare_modal_for_response',
        31 => 'process_form_submission',
        32 => 'get_default_config',
        33 => 'get_modal_schema',
        34 => 'get_form_submission_schema',
        35 => 'debug_cart_status',
        36 => 'check_coupon_applied',
        37 => 'get_cart_info_endpoint',
        38 => 'try_cart_access_strategies',
        39 => 'test_coupon_detection',
        40 => 'try_store_api_access',
        41 => 'try_cookie_session_access',
        42 => 'decode_wc_session_cookie',
        43 => 'get_session_data_from_db',
        44 => 'try_db_session_access',
        45 => 'get_applied_coupons_from_session',
        46 => 'clear_wc_session_cache',
        47 => 'is_coupon_applied_to_cart',
        48 => 'get_last_wc_filter_reason',
        49 => 'set_wc_filter_reason',
        50 => 'ensure_woocommerce_loaded',
        51 => 'ensure_cart_session',
        52 => 'get_cart_info_readonly',
        53 => 'get_modal_wc_config',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php' => 
    array (
      0 => '449216d600cc30caa61fbdbf327945974da9cb00',
      1 => 
      array (
        0 => 'ewm_performance',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'setup_caching',
        4 => 'conditional_asset_loading',
        5 => 'page_has_modals',
        6 => 'widgets_have_modals',
        7 => 'get_widget_content',
        8 => 'is_wc_page_with_modals',
        9 => 'optimize_asset_loading',
        10 => 'lazy_load_modals',
        11 => 'add_async_defer_attributes',
        12 => 'add_preload_hints',
        13 => 'cache_modal_config',
        14 => 'clear_modal_cache',
        15 => 'clear_related_cache',
        16 => 'optimize_queries',
        17 => 'add_version_to_assets',
        18 => 'get_performance_stats',
        19 => 'clear_all_cache',
        20 => 'get_performance_config',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce-endpoints.php' => 
    array (
      0 => 'b99e5611cc4000e2ccd449f5a88965dd57b9a66a',
      1 => 
      array (
        0 => 'ewm_woocommerce_endpoints',
      ),
      2 => 
      array (
        0 => 'register',
        1 => 'register_routes',
        2 => 'get_coupons',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php' => 
    array (
      0 => 'a4693b3d5244644970f22558d32f95fe6c85cf60',
      1 => 
      array (
        0 => 'ewm_wc_auto_injection',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'is_woocommerce_available',
        4 => 'is_product_page',
        5 => 'detect_product_page',
        6 => 'find_applicable_wc_modals',
        7 => 'test_modal_visibility',
        8 => 'get_modal_wc_config',
        9 => 'inject_wc_modals',
        10 => 'render_wc_modal',
        11 => 'inject_wc_triggers_script',
        12 => 'enqueue_scripts',
        13 => 'get_detected_modals',
        14 => 'get_current_product_id',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-general-auto-injection.php' => 
    array (
      0 => '39ec3e61df3a67f0ba2b013892ed8091e7af0c40',
      1 => 
      array (
        0 => 'ewm_general_auto_injection',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'get_instance',
        2 => 'init',
        3 => 'detect_current_page',
        4 => 'get_page_type',
        5 => 'find_applicable_general_modals',
        6 => 'should_show_modal_on_page',
        7 => 'check_woocommerce_restrictions',
        8 => 'check_page_rules',
        9 => 'check_device_rules',
        10 => 'check_user_role_rules',
        11 => 'register_shortcode_modal',
        12 => 'inject_general_modals',
        13 => 'render_general_modal',
        14 => 'inject_general_triggers_script',
        15 => 'get_detected_modals',
        16 => 'get_current_page_type',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php' => 
    array (
      0 => '8daf4663fe182ca2a692e11a55dfb98230bc77fe',
      1 => 
      array (
        0 => 'ewm_wc_compatibility_manager',
      ),
      2 => 
      array (
        0 => 'get_instance',
        1 => '__construct',
        2 => 'is_woocommerce_active',
        3 => 'is_wc_function_available',
        4 => 'is_wc_page',
        5 => 'is_product_page',
        6 => 'get_current_product_id',
        7 => 'get_currency',
        8 => 'is_cart_available',
        9 => 'apply_coupon_safe',
        10 => 'get_product_info_safe',
        11 => 'refresh_cache',
        12 => 'get_compatibility_status',
        13 => 'clear_cache',
        14 => 'show_wc_compatibility_notices',
        15 => 'is_ewm_admin_page',
        16 => 'has_wc_configured_modals',
      ),
      3 => 
      array (
      ),
    ),
  ),
));