<?php
/*
Plugin Name: Acesso Logs
Description: Registra acessos ao site.
*/
    function registrar_acesso() {
    //wpdb é uma forma de conectar com banco do wordpress
    global $wpdb;
    //aqui declara-se o nome do bd que queremos conectar do banco...
    $wp_security_logs = $wpdb->prefix . 'security_logs'; // Define o nome correto da tabela

    //$_server remote addr e http user agent... sao "funçoes" do proprio php puro que puxam informaçoes
    //do navegador da pessoa
    //o remote addr puxa o ip da pessoa que acessar, e o http user agent, a plataforma e mais algumas info de
    //por onde essa pessoa acessou, como chrome, windows etc.
    $ip_address = $_SERVER['REMOTE_ADDR'] ?: '127.0.0.1';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?: 'Navegador desconhecido';
    //deve ser algo padrao tao o current_time... traduzindo é tempo atual... entao pega o tempo atual do acesso
    //e adiciona na coluna log time da tabela security logs... eu acho
    $log_time = current_time('mysql');

    //$sql = "SELECT COUNT(*) as $ip_address and $log_time";
    //if (condition) {
        # code...
    //}

    //pega as informaçoes obtidas, adiciona a variavel resultado, variavel resultado insere nas tabelas do
    //banco wp_security_logs informaçoes obtidas
    $resultado = $wpdb->insert($wp_security_logs, [
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'log_time' => $log_time
    ]);
}
//executa a funçao
add_action('wp_head', 'registrar_acesso');

function contagem_ip() {
    global $wpdb;

    $wp_security_logs = $wpdb->prefix . 'security_logs';
    $wp_ips_bloqueados = $wpdb->prefix . 'ips_bloqueados';

    // Captura o IP atual de quem está acessando o site
    $ip_address  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // --- Verifica se o IP já está bloqueado ---
    $esta_bloqueado = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $wp_ips_bloqueados WHERE ip_address = %s",
            $ip_address
        )
    );

    // --- Se ainda NÃO está bloqueado ---
    if (!$esta_bloqueado) {

        // Conta quantos acessos esse IP fez nos últimos 5 minutos
        $total_acessos = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wp_security_logs WHERE ip_address = %s AND log_time >= (NOW() - INTERVAL 5 MINUTE)",
                $ip_address
            )
        );

        // Se acessou 10 vezes ou mais em 2 minutos → bloqueia!
        if ($total_acessos >= 10) {
            $wpdb->insert($wp_ips_bloqueados, [
                'ip_address' => $ip_address,
                'data_bloqueio' => current_time('mysql')
            ]);
        }
    }
//cria uma variavel que vai fazer a ponte... entre a tabela ips bloqueados e a gente, a variavel vai pegar 
//todos os ips da tabela ips bloqueados atraves da conexao com a chave global wpdb
//e pra fazer isso ele entra na tabela e conta atraves de um comando sql
//se achar algum ip la, ele atribui esse ip a variavel
$foi_bloqueado = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}ips_bloqueados WHERE ip_address = %s",
        $ip_address
    )
);
//se o ip que ta acessando estiver na tabela de bloqueados que foi verificado atraves da variavel "ponte"
//mata acesso de tudo com wp_die que a maneira padrao do wordpress...
if ($foi_bloqueado) {
  wp_die('O teu IP foi bloqueado irmão.');
}
}
add_action('wp_head', 'contagem_ip');

?>