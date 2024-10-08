<main>
    <div class="table ">
        <table class="datatable-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Vendor Name</th>
                    <th>Vendor Domain</th>
                    <th></th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $args = array(
                    'post_type' => 'wbs_vendor',
                );
                query_posts($args);
                if(have_posts()): while(have_posts()): the_post();?>
                    <tr>
                        <td></td>
                        <td><?php the_title()?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wbs-vendors&action=edit&vendor='.get_the_ID())?>">Edit</a>
                            <a href="<?php echo admin_url('admin.php?page=wbs-vendors&vendor='.get_the_ID())?>">View</a>
                            <a href="<?php echo admin_url('admin.php?page=wbs-vendors&action=edit&vendor='.get_the_ID())?>">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; wp_reset_query(); endif; ?>
            </tbody>
            <tfoot></tfoot>
        </table>
    </div>
</main>