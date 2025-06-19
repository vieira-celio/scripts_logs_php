<?php
/* Template Name: Logs de Acesso */
get_header();
//funçao do php current, aqui definimos que so "administrator" podem acessar a pagina de logs
//por que escrevendo administrator ele sabe quem é administrador eu nao sei
if (!current_user_can('administrator')) {
//wp_die deve ser um tipo de exibiçao de mensagem diferente pro wordpress
    wp_die('Você não tem permissão para acessar esta página.');
}

    global $wpdb;
    $wp_security_logs = $wpdb->prefix . 'security_logs'; // Ajuste conforme o nome correto no banco
    //logs é igual a tabela wp security logs.... usa o wpdb com funçao get_results pra puxar os resultados
    //dessa tabela, e essa tabela é "igual" a variavel logs
    $logs = $wpdb->get_results("SELECT * FROM $wp_security_logs ORDER BY log_time DESC");
    //se a variavel logs ta vazia... por causa desse simbolo !... nenhum registro...
    //se nao... se logs recebeu registros da tabela wp_security_logs atraves da funçao get_results... exibe
    //atraves de html, mas pra isso é preciso percorrer todos registros encontrados dentro da variavel logs...
?>

<h2>Logs de Acesso</h2>
<table border="1" style="width: 100%; margin-bottom: 30px;">
    <tr><th>IP</th><th>User Agent</th><th>Data/Hora</th></tr>
  
<?php
    if (!$logs) {
        echo "<tr><td colspan='3'>Nenhum registro encontrado.</td></tr>";
    } else {
        foreach ($logs as $log) {
            echo "<tr><td>{$log->ip_address}</td><td>{$log->user_agent}</td><td>{$log->log_time}</td></tr>";
        }
    }
?>
</table>

<h2>IPs Bloqueados</h2>
<div style="width: 700px; max-width: 100%; overflow-x: auto;">
<table border="1" style="width: 100%; table-layout: fixed; word-break: break-word;">
    <tr>
        <th style="width: 100%;">IP</th>
        <th style="width: 100%;">Data Bloqueio</th>
    </tr>
<?php
    //tentativa de inserir algum ip da tabela de ips bloqueados no template
    $wp_ips_bloqueados = $wpdb->prefix . 'ips_bloqueados';
    $bloqueados = $wpdb->get_results("SELECT * FROM $wp_ips_bloqueados ORDER BY data_bloqueio DESC");

    if (!$bloqueados) {
        echo "<tr><td colspan='3'>Nenhum registro encontrado.</td></tr>";
    } else {
        foreach ($bloqueados as $bloq) {
            echo "<tr><td>{$bloq->ip_address}</td><td>{$bloq->data_bloqueio}</td></tr>";
        }
    }
?>


</table>
</div>

<?php
get_footer(); 
?>