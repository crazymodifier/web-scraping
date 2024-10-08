<?php

$vendor_id = isset($_GET['vendor'])?intval($_GET['vendor']):'';


$products = get_posts(array('post_type'=>'wbs-product','post_parent'=>$vendor_id,'post_status'=>'draft'));
$wbs = new WebScarping();
$fields = $wbs->get_form_fiels();

// $wbs->print_pre($products);die;
if(isset($_GET['action']) && $_GET['action'] =='export'){
    
    $rows = $columns = [];
    $product = get_post_meta($products[0]->ID,'product_data',1);
    foreach($product as $key => $value){
        $columns[] = ucwords(str_replace('_'," ",$key));
    }
   
    $rows[] = $columns;
    foreach($products as $product){
        $columns = [];
        $product = get_post_meta($product->ID,'product_data',1);
        foreach($product as $key => $value){
            $columns[] = implode(',',$value);
        }
        $rows[] = $columns;
    }
    $fp = fopen(plugin_dir_path(__DIR__).'product.csv', 'w');
  
    // Loop through file pointer and a line
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    } 
    fclose($fp);

    echo '
    <script>
        jQuery(document).ready(function(){
            window.location.href = "'.plugin_dir_url(__DIR__).'product.csv";
        });</script>';
    // $wbs->print_pre($rows);
}

// die;
?>
<main>
    <div class="table ">
        <table class="datatable-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                <!--     <th>Vendor Name</th>
                    <th>Vendor Domain</th>
                    <th></th>
                    <th>Date</th>
                    <th>Action</th>
                 -->
                <?php 
                $product = get_post_meta($products[0]->ID,'product_data',1);
                foreach($product as $key => $value){?>
                    <th><?php echo ucwords(str_replace('_'," ",$key))?></th>
                <?php  }?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count =1;
                foreach($products as $product){
                    $product = get_post_meta($product->ID,'product_data',1);

                    ?>
                    <tr>
                        <td><?php echo $count++?></td>
                        <?php foreach($product as $key => $value){?>
                        <td><?php echo implode(',',$value)?></td>
                        <?php } ?>
                    </tr>
                <?php }?>
            </tbody>
            <tfoot></tfoot>
        </table>
    </div>

    <a href="admin.php?page=wbs-vendors&vendor=<?php echo $vendor_id?>&action=export">CSV Export</a>
</main>