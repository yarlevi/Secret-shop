<br/>
<br/>

<!-- Special version of Bootstrap that only affects content wrapped in .bootstrap-iso -->
<link rel="stylesheet" href="https://formden.com/static/cdn/bootstrap-iso.css" /> 

<div class="bootstrap-iso" dir="ltr">
    <div class="row">
        <div class="col-10" style="padding: 20px 30px">
            <h2>Priority Sync Settings  <a target="_blank" href="<?php echo get_option( 'prio_sync_url' ); ?>" class="btn btn-info">Go To Dashbord</a></h2>   
            <form method="post" action="options.php">     
                <?php settings_fields( 'priority-sync-settings' ); ?>     
                <?php do_settings_sections( 'priority-sync-settings' ); ?>  

                <h3>Api connection</h3>
                <div class="form-group">
                <label for="prio_sync_url">Priority WebApi Url (e.g https://my-gateway.com)</label>
                    <input value="<?php echo get_option( 'prio_sync_url' ); ?>" name="prio_sync_url" class="form-control" id="prio_sync_url" type="text">
                </div>

                <div class="form-group">
                    <label for="prio_sync_comp">External Api key</label>
                    <input value="<?php echo get_option( 'prio_sync_apikey' ); ?>" name="prio_sync_apikey" class="form-control" id="prio_sync_apikey" type="text">
                </div>

                <div class="form-group">
                    <label for="prio_sync_comp">Wordpress Api key</label>
                    <input value="<?php echo get_option( 'prio_sync_wp_apikey' ); ?>" name="prio_sync_wp_apikey" class="form-control" id="prio_sync_wp_apikey" type="text">
                </div>

                <hr/>

                <h3>Real Time Sync</h3>
                <div class="form-group">
                    <input type="checkbox" name="prio_sync_rt_categories" value="1" <?php checked(1, get_option('prio_sync_rt_categories'), true); ?> />
                    <label for="prio_sync_comp">Categories</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" name="prio_sync_rt_products" value="1" <?php checked(1, get_option('prio_sync_rt_products'), true); ?> />
                    <label for="prio_sync_comp">Products</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" name="prio_sync_rt_customers" value="1" <?php checked(1, get_option('prio_sync_rt_customers'), true); ?> />
                    <label for="prio_sync_comp">Customers</label>
                </div>

                <?php 
                    $order_type_options = array("invoice","order","receipt","none", "invoice_order");
                    $order_type = 'invoice';
                    if(get_option('prio_sync_order_type') != null){
                        $order_type = get_option('prio_sync_order_type');
                    }
                ?>   

                <div class="form-group">
                    <label for="prio_sync_comp">Send new  orders as:</label>
                    <select name="prio_sync_order_type" id="prio_sync_order_type" >
                        <?php
                            foreach($order_type_options as $option){
                                $selected = $order_type == $option;
                                if($selected){
                                    echo '<option selected value="'.$option.'">'.$option.'</option>';
                                }
                                else{
                                    echo '<option value="'.$option.'">'.$option.'</option>';
                                }
                            }
                        ?>  
                    </select>
                </div>

                <hr/>

                <?php 
                    $status_type_options = array("publish","draft");
                    $status_type = 'publish';
                    if(get_option('prio_sync_product_status') != null){
                        $status_type = get_option('prio_sync_product_status');
                    }
                ?>   

                <div class="form-group">
                    <label for="prio_sync_product_status">Open new product in status:</label>
                    <select name="prio_sync_product_status" id="prio_sync_product_status" >
                        <?php
                            foreach($status_type_options as $option){
                                $selected = $status_type == $option;
                                if($selected){
                                    echo '<option selected value="'.$option.'">'.$option.'</option>';
                                }
                                else{
                                    echo '<option value="'.$option.'">'.$option.'</option>';
                                }
                            }
                        ?>  
                    </select>
                </div>


                <?php 
                    $acf_order_name_error = '';
                    $acf_order_name = '';

                    if(is_plugin_active('advanced-custom-fields-pro/acf.php') || is_plugin_active('advanced-custom-fields/acf.php')){
                        $acf_order_name = get_option('prio_sync_order_type');
                    }
                    else{
						$acf_order_name_error ='בכדי לשמור מספר הזמנה מפריוריטי יש להתקין את התוסף: <br/> Advanced Custom Fields (שדות מיוחדים מתקדמים) <br/> לאחר ההתקנה יש לייצר עמודה בה יישמר המידע ולהכניס את השם שלה כאן.';
                    }
                    
                ?>

                <div class="form-group">
                    <label for="prio_sync_comp">Save Priority Order ID in ACF Field:</label>
                    <?php  
                        if($acf_order_name_error == ''){ ?>
                            <input value="<?php echo get_option( 'prio_sync_priority_order_name' ); ?>" name="prio_sync_priority_order_name" class="form-control" id="prio_sync_priority_order_name" type="text">
                        <?php }
                        else{ ?>
                            <div class="text-danger"><?php echo $acf_order_name_error ?></div>
                        <?php }
                    ?>
                </div>

                <hr/>
                <div class="form-group">
                    <input type="checkbox" name="prio_sync_rt_close" value="1" <?php checked(1, get_option('prio_sync_rt_close'), true); ?> />
                    <label for="prio_sync_comp">Sync Close Order</label>
                </div>

                <div class="form-group">
                    <input type="checkbox" name="prio_sync_rt_cancel" value="1" <?php checked(1, get_option('prio_sync_rt_cancel'), true); ?> />
                    <label for="prio_sync_comp">Sync Cancel Order</label>
                </div>

                <hr/>
                <div class="form-group">
                    <input type="checkbox" name="prio_sync_get_token" value="1" <?php checked(1, get_option('prio_sync_get_token'), true); ?> />
                    <label for="prio_sync_get_token">Sync Credit card token</label>
                </div>


                <div class="form-group">
                    <input type="checkbox" name="prio_sync_log_active" value="1" <?php checked(1, get_option('prio_sync_log_active'), true); ?> />
                    <label for="prio_sync_log_active">Send logs in sync (Send to: liel@qama.co.il)</label>
                </div>
                
                <?php submit_button(); ?>   
            </form>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($){
        
        var TestSettings = function (){

            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

            $.ajax ({
                type: "GET",
                url: ajaxurl,
                dataType: 'json',
                data:{
                    action : 'priority_sync_test' 
                },
                success: function (res){
                    try{
                        if(res.value.length > 0){
                            alert('Settings OK :)'); 
                            return;
                        }
                    }catch{}
                        alert('Settings Worng!!'); 
                },
                error: function(res){
                    alert('Settings Worng!!'); 
                    console.log(res);
                },
            });

        }

        $('#TestSettingsBtn').click(TestSettings)

    });
</script>