<?php

if(isset($_POST['submit']))
{
    $data = [];
    require plugin_dir_path(__DIR__).'vendor/autoload.php';
    $httpClient = \Symfony\Component\Panther\Client::createFirefoxClient();
    $postmeta = $_POST['postmeta'];
    $vendor_id = $_GET['vendor'];
    $urls = get_post_meta($vendor_id,'product_urls',1);
    // echo "<pre>";
    //     print_r($postmeta );
    // echo "</pre>";die;
    try {
        foreach($urls as $key => $url){
            if($key ==2){
                break;
            }
            // print_r(trim($url));
            foreach ($postmeta as $key => $value) {
    
                if(empty($value)){
                    $value = 'wbs-demo-data';
                } 
                if($key == 'product_images'){
                    // get response
                    $response = $httpClient->get($url);
                    $data[$url][$key] = $response->getCrawler()->filter($value)->each(function ($node) {
                        return $node->attr('href');
                    });
                }
                elseif($key == 'attribute_name' || $key == 'attribute_value'){
                    foreach($value as $count => $value_a){
                        // get response
                        if(empty($value_a)){
                            $value_a = 'wbs-demo-data';
                        } 
                        $response = $httpClient->get($url);
                        $data[$url][$key.'_'.$count] = $response->getCrawler()->filter($value_a)->each(function ($node) {
                            return $node->text();
                        });
                    }
                }
                else{
                    $response = $httpClient->get($url);
                    $data[$url][$key] = $response->getCrawler()->filter($value)->each(function ($node) {
                        return $node->text();
                    });
                }
                
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    } finally {
        $httpClient->quit();
    }
    
    
    
    // https://books.toscrape.com/
    echo "<pre>";
        print_r($data );
    echo "</pre>";
    $ids = [];
    foreach($data as $title => $item){

        $post_id = post_exists($title);
        if($post_id ){
            $postdata = array(
                'ID' => $post_id,
                'post_title' => $title,                
                'post_parent' => $vendor_id,
            );
            $post_id = wp_update_post($postdata);
        }
        else{
            $postdata = array(
                'post_title' => $title,
                'post_type' => 'wbs-product',
                'post_parent' => $vendor_id,
            );
            $post_id = wp_insert_post($postdata);
            
        }
        $ids[] = $post_id;
        update_post_meta($post_id,'product_data',$item);
    }
    // $old = get_post_meta($vendor_id,'product_data',1);
    // $data = array_merge($old,$data);
    update_post_meta($vendor_id,'product_data',$ids);
    die;
}

$vendor_id = isset($_GET['vendor'])?$_GET['vendor']:'';

if($vendor_id )
{
    $vendor = get_post($vendor_id);
    $vendordata =(object) get_post_meta($vendor_id,'post_data',1);
}

$wbs = new WebScarping();
$fields = $wbs->get_form_fiels();

// $wbs->print_pre($fields);
?>

<main>
    <div class="wbs-container">
        
        <div class="row">
            <div class="col-xl-3 col-lg-5 col-sm-6 ">
                <form action="" name="wbs-add-vendor" id="wbs-add-vendor" method="post">
                <input type="hidden" name="vendor" value="<?php echo (isset($_GET['vendor'])?$_GET['vendor']:'')?>">
                <?php foreach($fields['post'] as $key => $field) {
                    ?>
                    <div class="wbs-form-group">
                        <label class="wbs-label" for="<?php echo $field['name']?>">
                            <?php echo $field['title']?>
                        </label>
                        <input type="text" name="post[<?php echo $field['name']?>]" id="<?php echo $field['name']?>" value="<?php echo $vendor->post_title?>" class="wbs-input">
                       
                    </div>
                    <div class="wbs-form-group">
                        <label class="wbs-label" for="<?php echo $field['name']?>">
                            <?php echo $field['title']?>
                        </label>
                        <input type="text" name="post[<?php echo $field['name']?>]" id="<?php echo $field['name']?>" value="<?php echo $vendor->post_title?>" class="wbs-input">
                       
                    </div>
                    <hr>
                    <?php } foreach($fields['postmeta'] as $key => $field){ 
                        $name = $field['name'];
                        ?>
                    <div class="wbs-form-group">
                        <label class="wbs-label" for="<?php echo $name?>">
                            <?php echo $field['title']?>
                        </label>
                        <input type="text" name="postmeta[<?php echo $name?>]" id="<?php echo $name?>" value="<?php echo $vendordata->$name?>" class="wbs-input">
                    </div>
                    
                    <?php } ?>   
                    
                    <div class="wbs-repeater" data-number="0">
                        <div class="wbs-form-group">
                            <label class="wbs-label">
                                Attribute Name <span class="count">0</span>
                            </label>
                            <input type="text" name="postmeta[attribute_name][]" value="" class="wbs-input">
                        </div>
                        <div class="wbs-form-group">
                            <label class="wbs-label">
                                Attribute Values <span class="count">0</span>
                            </label>
                            <input type="text" name="postmeta[attribute_value][]" value="" class="wbs-input">
                        </div>
                    </div>
                    <button type="button" id="add-attr">Add New Attribute</button>
                <input type="button" name="save" id="save-add-vendor" value="Save & Submit">
                <input type="submit" name="submit" id="submit-add-vendor" value="Submit">
            </form>
            </div>
        </div>
    </div>
</main>
<script>

    jQuery(document).on('click','#add-attr',function(){
        var total = jQuery('.wbs-repeater').length;
        var item_n = total;
        jQuery(this).before('<div class="wbs-repeater" data-number="'+item_n+'"><div class="wbs-form-group"><label class="wbs-label">Attribute Name <span class="count">'+item_n+'</span></label><input type="text" name="postmeta[attribute_name][]" value="" class="wbs-input"></div><div class="wbs-form-group"><label class="wbs-label">Attribute Values <span class="count">'+item_n+'</span></label><input type="text" name="postmeta[attribute_value][]" value="" class="wbs-input"></div></div>');
    })
</script>