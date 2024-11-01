<?php
/*
Plugin Name: WPost Still Fresh
Description: 🇬🇧 Ultra-light plugin to keep your pages and posts always up to date for our friend GG. Set up a monthly/weekly CRON task (or at the recurrence you want...) and set the maximum number of previous days that you want. The plugin will set as the last modification date of your post/page, a random date between the execution date of your CRON task and the maximum number of previous days that you have configured. 🇫🇷 Petit plugin ultra léger pour garder vos pages et posts toujours à jour pour notre ami GG. Paramétrez une tache CRON mensuelle/hebdomadaire (ou à la récurrence que vous voulez...) et paramétrez le nombre de jour précédents max que vous souhaitez. Le plugin mettra comme date de dernière modification de votre post/page, une date aléatoire entre la date d'execution de votre tache CRON et le nombre de jour précédents max que vous avez paramétré.
Version: 1.1
Author: Kpsule Corporation
License: GPLv2
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

// Function Random Manuel
function chron0striggeuss_randomize_posts_ajax() {
    check_ajax_referer('randomize_posts_action', 'nonce');
    chron0striggeuss_randomize_post_dates();
    wp_send_json_success(__('✅ Mission ', 'wpost-still-fresh'));
}
add_action('wp_ajax_randomize_posts', 'chron0striggeuss_randomize_posts_ajax');


function chron0striggeuss_settings_section_cb() {
    echo '<p>Adjust the settings for posts and pages randomization schedules.</p>';
}

// Les callbacks pour afficher les champs de saisie dans l'interface d'administration
function chron0striggeuss_active_field_cb() {
    $option = get_option('chron0striggeuss_active', '0');
    echo '<select id="chron0striggeuss_active" name="chron0striggeuss_active">
            <option value="0" '.selected($option, '0', false).'>Inactive</option>
            <option value="1" '.selected($option, '1', false).'>Pages Only</option>
            <option value="2" '.selected($option, '2', false).'>Posts Only</option>
            <option value="3" '.selected($option, '3', false).'>Pages and Posts</option>
          </select>';
}

function chron0striggeuss_posts_interval_field_cb() {
    $value = get_option('chron0striggeuss_posts_interval', 30);
    echo '<div class="posts-config"><input type="number" id="chron0striggeuss_posts_interval" name="chron0striggeuss_posts_interval" value="' . esc_attr($value) . '" /></div>';
}

function chron0striggeuss_posts_backdate_field_cb() {
    $value = get_option('chron0striggeuss_posts_backdate', 29);
    echo '<div class="posts-config"><input type="number" id="chron0striggeuss_posts_backdate" name="chron0striggeuss_posts_backdate" value="' . esc_attr($value) . '" /></div>';
}

function chron0striggeuss_pages_interval_field_cb() {
    $value = get_option('chron0striggeuss_pages_interval', 30);
    echo '<div class="pages-config"><input type="number" id="chron0striggeuss_pages_interval" name="chron0striggeuss_pages_interval" value="' . esc_attr($value) . '" /></div>';
}

function chron0striggeuss_pages_backdate_field_cb() {
    $value = get_option('chron0striggeuss_pages_backdate', 29);
    echo '<div class="pages-config"><input type="number" id="chron0striggeuss_pages_backdate" name="chron0striggeuss_pages_backdate" value="' . esc_attr($value) . '" /></div>';
}

function chron0striggeuss_settings_page() {
    add_options_page(
        'WPost Still Fresh Settings',
        'WPost Still Fresh',
        'manage_options',
        'chron0striggeuss-custom-scheduler',
        'chron0striggeuss_settings_page_markup'
    );
}

add_action('admin_menu', 'chron0striggeuss_settings_page');

function chron0striggeuss_enqueue_admin_scripts($hook_suffix) {
   
  if ($hook_suffix === 'settings_page_chron0striggeuss-custom-scheduler') {
    wp_register_script('chron0striggeuss-admin-script', plugins_url('js/chronocore.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('chron0striggeuss-admin-script', 'chron0striggeussParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('chron0striggeuss_randomize_posts_nonce')
    ));
    wp_enqueue_script('chron0striggeuss-admin-script');
   }
	
}

add_action('admin_enqueue_scripts', 'chron0striggeuss_enqueue_admin_scripts');


function chron0striggeuss_settings_page_markup() {
    if (!current_user_can('manage_options')) {
        return;
    }
	settings_errors('chron0striggeuss_messages');


    ?>
    <div class="wrap">
        <h1><?php esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('chron0striggeuss');
            do_settings_sections('chron0striggeuss');
            submit_button('Save Settings');
            ?>
        </form>
		<form method="post" class="randomize-posts-form">
			<input type="hidden" id="randomize_posts_nonce" name="nonce" value="<?php echo esc_attr(wp_create_nonce('randomize_posts_action')); ?>">
			<input type="submit" name="randomize_now" value="Refresh NOW!" class="button button-primary">
		</form>
		<div id="message-container" style="display:none; padding: 10px; color: #fff;margin: 20px 0; border-radius: 8px;"></div>

    </div>
    <?php
}

function chron0striggeuss_register_settings() {
	register_setting('chron0striggeuss', 'chron0striggeuss_active');
    register_setting('chron0striggeuss', 'chron0striggeuss_posts_interval', 'intval');
    register_setting('chron0striggeuss', 'chron0striggeuss_posts_backdate', 'intval');
	register_setting('chron0striggeuss', 'chron0striggeuss_pages_interval', 'intval');
    register_setting('chron0striggeuss', 'chron0striggeuss_pages_backdate', 'intval');
    
    add_settings_section(
        'chron0striggeuss_settings_section',
        'Scheduler Settings',
        'chron0striggeuss_settings_section_cb',
        'chron0striggeuss'
    );

    add_settings_field(
        'chron0striggeuss_active_field',
        'Activate Scheduler',
        'chron0striggeuss_active_field_cb',
        'chron0striggeuss',
        'chron0striggeuss_settings_section'
    );

    add_settings_field(
        'chron0striggeuss_posts_interval_field',
        'Posts Cron Interval (days)',
        'chron0striggeuss_posts_interval_field_cb',
        'chron0striggeuss',
        'chron0striggeuss_settings_section'
    );
	
	 add_settings_field(
        'chron0striggeuss_posts_backdate_field',
        'Posts Max Backdate (days)',
        'chron0striggeuss_posts_backdate_field_cb',
        'chron0striggeuss',
        'chron0striggeuss_settings_section'
    );

    add_settings_field(
        'chron0striggeuss_pages_interval_field',
        'Pages Cron Interval (days)',
        'chron0striggeuss_pages_interval_field_cb',
        'chron0striggeuss',
        'chron0striggeuss_settings_section'
    ); 

    add_settings_field(
        'chron0striggeuss_pages_backdate_field',
        'Pages Max Backdate (days)',
        'chron0striggeuss_pages_backdate_field_cb',
        'chron0striggeuss',
        'chron0striggeuss_settings_section'
    );
}

add_action('admin_init', 'chron0striggeuss_register_settings');


function chron0striggeuss_randomize_post_dates() {
	
	$isActive = get_option('chron0striggeuss_active', '0');
	
    $post_types = array();
    $max_days_ago = 29; // Valeur par défaut si aucune configuration n'est définie
	
	
	if ($isActive === '1') {
        $post_types = array('page');
        $max_days_ago = get_option('chron0striggeuss_pages_backdate', 29);
    } elseif ($isActive === '2') {
        $post_types = array('post');
        $max_days_ago = get_option('chron0striggeuss_posts_backdate', 29);
    } elseif ($isActive === '3') {
        $post_types = array('post', 'page');
        $max_days_ago = max(get_option('chron0striggeuss_posts_backdate', 29), get_option('chron0striggeuss_pages_backdate', 29));
    }

    if (!empty($post_types)) {
        $args = array(
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1
		);

		$posts = get_posts($args);
		
		foreach ($posts as $post) {
			
			// Appliquer un max_backdate spécifique si les deux types sont mélangés
            if ($isActive === '3') {
                $max_days_ago = 'post' === $post->post_type ? get_option('chron0striggeuss_posts_backdate', 29) : get_option('chron0striggeuss_pages_backdate', 29);
            }
			
			$date = new DateTime();
			$days_ago = rand(1, $max_days_ago);
			$hours = rand(0, 23);
			$minutes = rand(0, 59);
			$seconds = rand(0, 59);
			$date->modify("-{$days_ago} days {$hours} hours {$minutes} minutes {$seconds} seconds");
			$random_date = $date->format('Y-m-d H:i:s');
			
			
			add_filter('wp_insert_post_data', function($data, $postarr) use ($post, $random_date) {
            if ($postarr['ID'] == $post->ID) {
                error_log('Filter executed for post ID: ' . $post->ID);
                $data['post_modified'] = $random_date;
                $data['post_modified_gmt'] = get_gmt_from_date($random_date);
            }					return $data;
			}, 10, 2);
			
			wp_update_post(array(
				'ID' => $post->ID
			));

			remove_filter('wp_insert_post_data', 'modify_post_modified_date', 10);
			
		}
	
	} 
}

function chron0striggeuss_add_monthly_cron_schedule($schedules) {
    // Récupérer les intervalles pour les posts et les pages
    $post_interval = get_option('chron0striggeuss_posts_interval', 30); // Valeur par défaut de 30 jours
    $page_interval = get_option('chron0striggeuss_pages_interval', 30); // Valeur par défaut de 30 jours

    // Convertir les intervalles en secondes
    $post_interval_in_seconds = (int) $post_interval * DAY_IN_SECONDS;
    $page_interval_in_seconds = (int) $page_interval * DAY_IN_SECONDS;

    // Ajouter un schedule pour les posts
    $schedules['chron0striggeuss_posts_fire'] = array(
        'interval' => $post_interval_in_seconds,
        'display'  => __('Chron0striggeuss Posts Interval', 'wpost-still-fresh')
    );

    // Ajouter un schedule pour les pages
    $schedules['chron0striggeuss_pages_fire'] = array(
        'interval' => $page_interval_in_seconds,
        'display'  => __('Chron0striggeuss Pages Interval', 'wpost-still-fresh')
    );

    return $schedules;
}

add_filter('cron_schedules', 'chron0striggeuss_add_monthly_cron_schedule');

function chron0striggeuss_setup_monthly_cron_event() {
    $post_cron_hook = 'chron0striggeuss_post_randomize';
    $page_cron_hook = 'chron0striggeuss_page_randomize';
    
    $timestamp_post = wp_next_scheduled($post_cron_hook);
    $timestamp_page = wp_next_scheduled($page_cron_hook);
    
    $isActive = get_option('chron0striggeuss_active', '0');
    $current_post_interval = get_option('chron0striggeuss_posts_interval', 30);
    $current_page_interval = get_option('chron0striggeuss_pages_interval', 30);

    $stored_post_interval = get_option('chron0striggeuss_stored_post_interval', 30);
    $stored_page_interval = get_option('chron0striggeuss_stored_page_interval', 30);


 // Désactivation de toutes les tâches Cron si $isActive est égal à 0
    if ($isActive == '0') {
       if ($timestamp_page) {
            wp_unschedule_event($timestamp_page, $page_cron_hook);
       }
        if ($timestamp_post) {
            wp_unschedule_event($timestamp_post, $post_cron_hook);
        }
        return; // Rien de plus à faire, retirerons tout car $isActive est 0
    }
    
    // Désactiver les crons non nécessaires
    if ($isActive === '2' && $timestamp_page) { // Pas nécessaire d'avoir une cron pour les posts
        wp_unschedule_event($timestamp_page, $page_cron_hook);
    }

    if ($isActive === '1' && $timestamp_post) { // Pas nécessaire d'avoir une cron pour les pages
        wp_unschedule_event($timestamp_post, $post_cron_hook);
    }
	
	
    // Traitement des crons pour les pages
    if ($isActive === '1' || $isActive === '3') {
        if (!$timestamp_page || $current_page_interval !== $stored_page_interval) {
            if ($timestamp_page) {
                wp_unschedule_event($timestamp_page, $page_cron_hook);
            }
            wp_schedule_event(time(), 'chron0striggeuss_pages_fire', $page_cron_hook);
            update_option('chron0striggeuss_stored_page_interval', $current_page_interval);
        }
    } else {
        if ($timestamp_page) {
            wp_unschedule_event($timestamp_page, $page_cron_hook);
        }
    }

    // Traitement des crons pour les articles
    if ($isActive === '2' || $isActive === '3') {
        if (!$timestamp_post || $current_post_interval !== $stored_post_interval) {
            if ($timestamp_post) {
                wp_unschedule_event($timestamp_post, $post_cron_hook);
            }
            wp_schedule_event(time(), 'chron0striggeuss_posts_fire', $post_cron_hook);
            update_option('chron0striggeuss_stored_post_interval', $current_post_interval);
        }
    } else {
        if ($timestamp_post) {
            wp_unschedule_event($timestamp_post, $post_cron_hook);
        }
    }
}

add_action('init', 'chron0striggeuss_setup_monthly_cron_event');