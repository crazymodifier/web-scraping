<?php

$vendor_id = isset($_GET['vendor'])?intval($_GET['vendor']):'';
$products = get_post_meta($vendor_id,'product_data',1);
$wbs = new WebScarping();
$fields = $wbs->get_form_fiels();

if(isset($_GET['action']) && $_GET['action'] =='export'){
    $rows = $columns = [];
    ob_clean();
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=data.csv');  
    foreach($fields['postmeta'] as $title){
        $columns[] = $title['title'];
    }
    $rows[] = $columns;
    foreach($products as $product){
        $columns = [];
        foreach($product as $key => $value){
            $columns[] = $value[0];
        }
        $rows[] = $columns;
    }
    $fp = fopen('php://output', 'w');
  
    // Loop through file pointer and a line
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    } 
    fclose($fp);
    ob_flush();
    exit;
    // $wbs->print_pre($rows);
}
?>