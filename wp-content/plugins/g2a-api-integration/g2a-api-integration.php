<?php
/**
 * Plugin Name: G2A API Integration
 * Description: Plugin para integrar com a G2A API.
 * Version: 1.0
 * Author: Seu Nome
 */

// Adiciona uma página de menu no painel do WordPress
add_action('admin_menu', 'g2a_api_menu');

function g2a_api_menu() {
    add_menu_page('G2A API', 'G2A API', 'manage_options', 'g2a-api', 'g2a_api_page');
}

function g2a_api_page() {
    ?>
    <div class="wrap">
        <h1>Integração com a G2A API</h1>
        <form method="post" action="">
            <input type="submit" name="g2a_get_token" value="Obter Token de Acesso">
        </form>

        <?php
        if (isset($_POST['g2a_get_token'])) {
            g2a_get_access_token();
        }
        ?>
    </div>
    <?php
}

function g2a_get_access_token() {
    $url = "https://api.g2a.com/v1/token";
    
    $data = array(
        'client_id' => 'nAxqxnnouJiWkTVw', // Seu Client ID
        'client_secret' => '74026b3dc2c6db6a30a73e71cdb138b1e1b5eb7a97ced46689e2d28db1050875' // Seu Client Secret
    );

    $options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n" .
                         "Accept: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo '<div class="error"><p>Error retrieving token</p></div>';
        return;
    }

    $response = json_decode($result, true);
    
    if (isset($response['status']) && $response['status'] === 'ERROR') {
        echo '<div class="error"><p>' . esc_html($response['message']) . '</p></div>';
    } else {
        $token = $response['access_token']; // O token de autorização
        echo '<div class="updated"><p>Token de Acesso: ' . esc_html($token) . '</p></div>';
    }
}
