<?php
/**
 * Email Template Part: Footer
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
        </div>
        <div class="email-footer">
            <p><?php printf( __( 'This email was sent from %s', 'vitapro-appointments-fse' ), '<a href="' . esc_url( $args['site_url'] ?? home_url() ) . '">' . esc_html( $args['site_name'] ?? get_bloginfo( 'name' ) ) . '</a>' ); ?></p>
            <p><?php _e( 'If you have any questions, please contact us.', 'vitapro-appointments-fse' ); ?></p>
        </div>
    </div>
</body>
</html>