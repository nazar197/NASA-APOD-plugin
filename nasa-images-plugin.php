<?php
/*
Plugin Name: NASA Images Plugin
Description: Display NASA's Images of the Day for the last 15 days.
Version: 1.0
Author: Nazar Pidhrushnyi
Author URI: https://github.com/nazar197
*/

// Receive data from NASA API
function get_data_from_nasa() {
  define('NASA_API_KEY', 'YOUR_NASA_API_KEY');
  define('DAYS_TO_FETCH', 15);

  $end_date = date('Y-m-d');
  $start_date = date('Y-m-d', strtotime("-" . DAYS_TO_FETCH . " days"));

  $url = 'https://api.nasa.gov/planetary/apod?api_key=' . NASA_API_KEY . '&start_date=' . $start_date . '&end_date=' . $end_date;

  $response = wp_remote_get($url);

  if (is_array($response) && !is_wp_error($response)) {
    $data = json_decode($response['body']);
    return $data;
  }

  return false;
}

// Generate a table with NASA data
function generate_page_content() {
  $nasa_data = get_data_from_nasa();

  if ($nasa_data) {
    $nasa_data = array_slice($nasa_data, 0, 15);
    ob_start();
?>
    <table class="table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Description</th>
          <th>Author</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($nasa_data as $item) : ?>
          <tr>
            <td><img src="<?php echo $item->url; ?>" alt="<?php echo $item->title ? $item->title : 'The image is not found'; ?>" width="150"></td>
            <td><strong><?php echo $item->title; ?></strong><br><?php echo $item->explanation; ?></td>
            <td><strong><?php echo isset($item->copyright) ? $item->copyright : 'The unknown author'; ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
<?php
    return ob_get_clean();
  } else {
    return 'Error during receiving data from API NASA!';
  }
}

// Add page to the admin dashboard
function create_page() {
  $page_title = 'Astronomy Picture of the Day';
  $page_content = generate_page_content();

  $existing_page = get_page_by_title($page_title);
  if (!$existing_page) {
    $new_page = array(
      'post_title' => $page_title,
      'post_content' => $page_content,
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => sanitize_title($page_title)
    );

    wp_insert_post($new_page);
  }
}

add_action('admin_init', 'create_page');

// Add Bootstrap styles
function add_bootstrap_styles() {
  wp_enqueue_style('bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
}

add_action('wp_enqueue_scripts', 'add_bootstrap_styles');
