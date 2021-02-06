<?php
/*
Plugin Name: Geonames Plugin
Plugin URI:  https://www.abc.com/
Description: Get countries and cities list
Version:     1.0.0
Author:      Payal chovatiya
Author URI:  https://www.abc.com/
Textdomain : geoname
*/

class geonames{
    public function __construct(){
        add_action( 'admin_menu', array($this,'geonames_add_settings_page') );
        add_action('admin_enqueue_scripts', array($this,'enqueue_front_scripts_and_styles'));
    }
    
    public function enqueue_front_scripts_and_styles(){
        wp_enqueue_style( 'geoname-bootstrap-css', plugins_url( '/assets/css/bootstrap.min.css', __FILE__ ));
        wp_enqueue_style( 'geoname-datatables-css', plugins_url( '/assets/css/datatables.min.css', __FILE__ ));
        wp_enqueue_script( 'geoname-bootstrap-js', plugins_url( '/assets/js/bootstrap.min.js', __FILE__ ),array(),time());
        wp_enqueue_script( 'geoname-datatables-js', plugins_url( '/assets/js/datatables.min.js', __FILE__ ),array(),time());
    }

    //Set menu admin menu
    public function geonames_add_settings_page() {
        add_menu_page(
            __( 'Geonames', 'geoname' ),
            'Geonames',
            'manage_options',
            'myplugin/myplugin-admin.php',
            array( $this,'geoname_callback_function')
                
        );
    }
    
    //api for get country and city
    public function get_api_response($api_url) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        
        $countries_names = json_decode($response);
        
        return $countries_names;
    }
    
    //callback function for country
    public function geoname_callback_function(){
        $curl = curl_init();

//        $state_list = 'https://countriesnow.space/api/v0.1/countries/states';
        $country_list = 'https://countriesnow.space/api/v0.1/countries';
        
        
        if( isset($_POST['geoname-submit']) ) {
            $country_index = $_POST['country'];
        }
        
        $countries_lists = $this->get_api_response($country_list);
    ?>
        <div class="container py-5">
            <div class="row">
                <div class="col-md-10 mx-auto">
                    <form name="geoname" method="post">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="city" class="form-control city" placeholder="City">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?php if( !empty($countries_lists) ) { ?>
                                            <select name="country" class="form-control country ">
                                                <option value="all">all countries</option>
                                                <?php foreach ( $countries_lists->data as $key => $countries ) { ?>
                                                        <option value="<?php echo $key; ?>"><?php echo $countries->country; ?></option>  
                                                <?php } ?>
                                            </select>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="geoname-submit" class="btn btn-primary geoname-submit" value="Search">
                    </form>
                </div>
            </div>
           
        </div>
        <?php if( !empty($country_index) ) { ?>
                <table id="georesult" class="table table-striped table-bordered table-sm">
                    <thead>
                      <tr>
                        <th class="th-sm">No.</th>
                        <th class="th-sm">City Name</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if( $country_index == 'all' ){
                        foreach ( $countries_lists->data as $key => $city_list ) {
                            foreach ( $city_list->cities as $c_key => $city ) {
                                if ($c_key > 50) { break; } 
                    ?>
                            <tr>
                                <th scope="row"><?php echo $c_key + 1; ?></th>
                                <td><?php echo $city; ?></td>
                            </tr>
                        <?php } 
                        }
                    ?>
                    
                    <?php } else {
                        foreach ( $countries_lists->data[$country_index]->cities as $key => $countries ) { ?>
                            <tr>
                                <th scope="row"><?php echo $key + 1; ?></th>
                                <td><?php echo $countries; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
    <?php } ?>
<script>
    jQuery('#georesult').DataTable();
</script>
        <?php } ?>
    <?php
    }
}
new geonames();


