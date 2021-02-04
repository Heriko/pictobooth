<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Organizer Payments class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_op extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var array
     */
    public $gateways_options;

    /**
     * @var array
     */
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();

        // Gateways Options
        $this->gateways_options = $this->main->get_gateways_options();

        // General Options
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Pro version is required
        if(!$this->getPRO()) return;

        // Module is not enabled
        if(!isset($this->gateways_options['op_status']) or (isset($this->gateways_options['op_status']) and !$this->gateways_options['op_status'])) return;

        // Register Meta Box
        $booking_status = (isset($this->settings['booking_status']) and $this->settings['booking_status']) ? true : false;
        if($booking_status)
        {
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_op'), 25);

            // Booking Options for FES
            if(!isset($this->settings['fes_section_booking']) or (isset($this->settings['fes_section_booking']) and $this->settings['fes_section_booking'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_op'), 50);
        }

        // Filter Payment Gateway Options
        $this->factory->filter('mec_gateway_options', array($this, 'op'), 10, 3);
    }

    /**
     * To show payment options
     * @param object $post
     */
    public function meta_box_op($post)
    {
        global $current_screen;

        // Organizer Payment Option
        $op_options = get_post_meta($post->ID, 'mec_op', true);
        if(!is_array($op_options)) $op_options = array();

        // Get Default Values From User
        if(isset($current_screen->action) and $current_screen->action == 'add' and !count($op_options)) $op_options = get_user_meta(get_post_field('post_author', $post->ID), 'mec_op', true);
        if(!is_array($op_options)) $op_options = array();

        $gateways = $this->main->get_gateways();
        ?>
        <div id="mec-organizer-payments">
            <div class="mec-meta-box-fields mec-booking-tab-content" id="mec_meta_box_op_form">
                <h4 class="mec-title"><?php _e('Organizer Payment Credentials', 'mec'); ?></h4>
                <div class="mec-form-row" id="mec_organizer_gateways_form_container">
                    <ul>
                        <?php foreach($gateways as $gateway): if(!$gateway->enabled() or !$gateway->op_enabled()) continue; ?>
                            <li id="mec_gateway_id<?php echo $gateway->id(); ?>">
                                <?php $gateway->op_form((isset($op_options[$gateway->id()]) ? $op_options[$gateway->id()] : array())); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * @param array $options
     * @param int $gateway_id
     * @param string $transaction_id
     * @return mixed
     */
    public function op($options, $gateway_id, $transaction_id)
    {
        // It's not related to a transaction so return them without change
        if(!$transaction_id) return $options;

        // It's not related to OP enabled gateways
        if(!in_array($gateway_id, array(5,2,3))) return $options;

        $book = $this->getBook();
        $transaction = $book->get_transaction($transaction_id);

        $event_id = isset($transaction['event_id']) ? $transaction['event_id'] : 0;

        // Event Not Found!
        if(!$event_id) return $options;

        // Organizer Payment Options
        $op = get_post_meta($event_id, 'mec_op', true);

        // No Organizer Option Found
        if(!is_array($op) or (is_array($op) and !count($op))) return $options;

        // Organizer Gateway Options
        $gateway_op = isset($op[$gateway_id]) ? $op[$gateway_id] : array();

        // No Organizer Option Found for Gateway
        if(!is_array($gateway_op) or (is_array($gateway_op) and !count($gateway_op))) return $options;

        // Filter Options
        foreach($gateway_op as $key=>$value)
        {
            if(trim($value) == '' or !isset($options[$key])) continue;

            // Overwrite the Option
            $options[$key] = $value;
        }

        // Return Filtered Options
        return $options;
    }
}