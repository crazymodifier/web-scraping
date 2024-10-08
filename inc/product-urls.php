<?php

if(isset($_POST['submit']))
{
    $output = $data = [];
    require plugin_dir_path(__DIR__).'vendor/autoload.php';
    $vendor_id = $_POST['vendor'];
    $shop_url = $_POST['shop_url'];
    $wait_for = $_POST['wait_for'];
    $value = $_POST['url_anchor'];
    $page = $_POST['page'];

    update_post_meta($vendor_id,'url_form',$_POST);
    try {
        if($shop_url){
            $httpClient = \Symfony\Component\Panther\Client::createFirefoxClient();
            // get response
            $response = $httpClient->get($shop_url);
            $data[] = $response->getCrawler()->filter($value)->each(function ($node) {
                return $node->attr('href');
            });
        }
    
        for($i=2;$i<=$page;$i++){
            $httpClient->clickLink($i);
            $response = $httpClient->waitFor($wait_for);
            $response = $httpClient->waitForVisibility($wait_for);
    
            $data[] = $response->filter($value)->each(function ($node) {
                return $node->attr('href');
            });
        }
        
    
        foreach($data as $page)
        {
            foreach($page as $url){
                $output[] = $url;
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    finally{
        $httpClient->quit();
    }
    
    echo "<pre>";
        print_r($output);
    echo "</pre>";

    add_post_meta($vendor_id,'product_urls',$output);
    die;
}


$vendor_id = isset($_GET['vendor'])?$_GET['vendor']:'';

if($vendor_id )
{
    $urldata =(object) get_post_meta($vendor_id,'url_form',1);
}

$wbs = new WebScarping();
?>

<main>
    <div class="wbs-container">
        <form action="" name="product-urls" id="product-urls" method="post">
            <!-- <input type="hidden" name="vendor" value="<?php //echo (isset($_GET['vendor'])?$_GET['vendor']:'')?>"> -->
            <div class="row">
                <div class="col-xl-2 col-sm-3">
                    <label class="wbs-label" for="vendor">
                        Select Vendor
                    </label>
                </div>
                <div class="col-xl-4 col-sm-9">
                    <select name="vendor" id="vendor">
                        <option value="">Select Vendor</option>
                        <?php 
                        $args = array(
                            'post_type' => 'wbs_vendor',
                        );
                        query_posts($args);
                        if(have_posts()): while(have_posts()): the_post();?>
                            <option value="<?php the_ID()?>" <?php echo ($urldata->vendor == get_the_ID())?'selected':''?>><?php the_title()?></option>
                        <?php endwhile; wp_reset_query(); endif; ?>

                    </select>
                </div>
            </div>  
            <div class="row">
                <div class="col-xl-2 col-sm-3">
                    <label class="wbs-label" for="shop_url">
                        Product Shop Page URL
                    </label>
                </div>
                <div class="col-xl-4 col-sm-9">
                    <input type="text" id="shop_url" name="shop_url" value="<?php echo $urldata->shop_url?>">
                </div>
                <!-- https://barknbag.com/shop/ -->
            </div> 
            <div class="row">
                <div class="col-xl-2 col-sm-3">
                    <label class="wbs-label" for="url_anchor">
                        Product URL CSS Selector
                    </label>
                </div>
                <div class="col-xl-4 col-sm-9">
                    <input type="text" id="url_anchor" name="url_anchor" value="<?php echo $urldata->url_anchor?>">
                </div>
            </div> 
            <div class="row">
                <div class="col-xl-2 col-sm-3">
                    <label class="wbs-label" for="page">
                        Number of Pages
                    </label>
                </div>
                <div class="col-xl-4 col-sm-9">
                    <input type="text" id="page" name="page" value="<?php echo $urldata->page?>">
                </div>
            </div> 
            <div class="row">
                <div class="col-xl-2 col-sm-3">
                    <label class="wbs-label" for="wait_for">
                        Wait For
                    </label>
                </div>
                <div class="col-xl-4 col-sm-9">
                    <input type="text" id="wait_for" name="wait_for" value="<?php echo $urldata->wait_for?>">
                </div>
            </div>         
            <input type="submit" name="submit" id="save-form" value="Submit">
        </form>
    </div>
</main>
