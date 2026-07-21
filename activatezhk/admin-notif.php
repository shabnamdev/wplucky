<?php
defined('ABSPATH') || exit ("no access");
if( empty($this->c15fd51e29734304c8302b) ): ?>
    <div class="notice notice-error">
        <?php if (version_compare(PHP_VERSION, '7.0.0') >= 0):?>
        <p>
            <?php printf(esc_html__( 'To activating %s, please insert your license key', 'guard-gn-fe2aa6cdddea634567904d231acd72f' ), esc_html__($this->cfb99ca738e02e805ab686fbc, 'guard-gn-fe2aa6cdddea634567904d231acd72f')); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->b4dcc45f9ce1f8edc40166af72f6c ); ?>" class="button button-primary"><?php _e('Active License', 'guard-gn-fe2aa6cdddea634567904d231acd72f'); ?></a>
        </p>
        <?php else:?>
            <p>
                <?php printf(esc_html__( 'The PHP version of the website is lower than 7.0. Ask your host administrator to upgrade PHP version to activate %s. ', 'guard-gn-fe2aa6cdddea634567904d231acd72f' ), esc_html__($this->cfb99ca738e02e805ab686fbc, 'guard-gn-fe2aa6cdddea634567904d231acd72f')); ?>
            </p>
    <?php endif; ?>
    </div>
<?php elseif( $this->d8e22cdb0a7cee72fab82427b779c===true ): ?>
    <div class="notice notice-error">
        <p>
            <?php printf(esc_html__( 'Something is wrong with your %s license. Please check it.', 'guard-gn-fe2aa6cdddea634567904d231acd72f' ), esc_html__($this->cfb99ca738e02e805ab686fbc, 'guard-gn-fe2aa6cdddea634567904d231acd72f')); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->b4dcc45f9ce1f8edc40166af72f6c ); ?>" class="button button-primary"><?php _e('Check Now', 'guard-gn-fe2aa6cdddea634567904d231acd72f'); ?></a>
        </p>
    </div>
<?php endif; ?>