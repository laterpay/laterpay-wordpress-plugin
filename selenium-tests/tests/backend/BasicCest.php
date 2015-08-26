<?php

use \BackendTester;

/**
 * Installation
 * @group C1
 */
class BasicCest {

    public function userCanPurchasePPUItemWithCurrentRelease( BackendTester $I ) {
        $I->wantToTest( 'User Can Purchase PPU Item With Current Release' );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->installPlugin()
            ->activatePlugin();

        $post_args = array(
            'price'      => 0.05,
            'price_type' => 'Individual',
        );

        PostModule::of( $I )
            ->createPost( $post_args );

        $post_id = $I->getVar( 'post' );

        AuthModule::of( $I )
            ->logout();

        $after_purchase_options = array(
            'fulltext_visible'        => true,
            'teaser_visible'          => false,
            'purchase_button_visible' => false,
            'overlay_visible'         => false,
            'purchase_link_visible'   => false,
            'timepasses_visible'      => false,
        );

        PostModule::of( $I )
            ->checkPost( $post_id )
            ->purchasePost( $post_id )
            ->checkPost( $post_id, null, $after_purchase_options );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->deactivatePlugin()
            ->deletePlugin();

        AuthModule::of( $I )
            ->logout();
    }

    public function userCanPurchasePPUTimePassWithCurrentRelease( BackendTester $I ) {
        $I->wantToTest( 'User Can Purchase PPU Time Pass With Current Release' );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->installPlugin()
            ->activatePlugin();

        $post_args = array(
            'price'      => 0.05,
            'price_type' => 'Individual',
        );

        PostModule::of( $I )
            ->createPost( $post_args );

        $timepass_args = array(
            'price' => 5,
        );

        TimepassModule::of( $I )
            ->createTimepass( $timepass_args );

        $post_id = $I->getVar( 'post' );

        AuthModule::of( $I )
            ->logout();

        $after_purchase_options = array(
            'fulltext_visible'        => true,
            'teaser_visible'          => false,
            'purchase_button_visible' => false,
            'overlay_visible'         => false,
            'purchase_link_visible'   => false,
            'timepasses_visible'      => false,
        );

        PostModule::of( $I )
            ->checkPost( $post_id );

        TimepassModule::of( $I )
            ->purchaseTimepass( $post_id );

        PostModule::of( $I )
            ->checkPost( $post_id, null, $after_purchase_options );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->deactivatePlugin()
            ->deletePlugin();

        AuthModule::of( $I )
            ->logout();
    }

    public function userCanAccessIndividualPPUPurchaseAfterPluginUpdate( BackendTester $I ) {
        $I->wantToTest( 'User Can Access Individual PPU Purchase That Was Purchased Before Update' );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->installPlugin( '0.9.11.3' )
            ->activatePlugin();

        $post_args = array(
            'price'      => 0.05,
            'price_type' => 'Individual',
        );

        PostModule::of( $I )
            ->createPost( $post_args );

        $post_id = $I->getVar( 'post' );

        AuthModule::of( $I )
            ->logout();

        $after_purchase_options = array(
            'fulltext_visible'        => true,
            'teaser_visible'          => false,
            'purchase_button_visible' => false,
            'overlay_visible'         => false,
            'purchase_link_visible'   => false,
            'timepasses_visible'      => false,
        );

        PostModule::of( $I )
            ->checkPost( $post_id )
            ->purchasePost( $post_id );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->deactivatePlugin()
            ->deletePlugin()
            ->installPlugin( '0.9.11.4' )
            ->activatePlugin();

        AuthModule::of( $I )
            ->logout();

        PostModule::of( $I )
            ->checkPost( $post_id, null, $after_purchase_options );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->deactivatePlugin()
            ->deletePlugin();

        AuthModule::of( $I )
            ->logout();
    }

    public function userCanAccessIndividualPPUTimePassPurchaseAfterPluginUpdate( BackendTester $I ) {
        $I->wantToTest( 'User Can Access Individual PPU Time Pass Purchase That Was Purchased Before Update' );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->installPlugin( '0.9.11.3' )
            ->activatePlugin();

        $post_args = array(
            'price'      => 0.05,
            'price_type' => 'Individual',
        );

        PostModule::of( $I )
            ->createPost( $post_args );

        $post_id = $I->getVar( 'post' );

        $timepass_args = array(
            'price' => 5
        );

        TimepassModule::of( $I )
            ->createTimepass( $timepass_args );

        AuthModule::of( $I )
            ->logout();

        $after_purchase_options = array(
            'fulltext_visible'        => true,
            'teaser_visible'          => false,
            'purchase_button_visible' => false,
            'overlay_visible'         => false,
            'purchase_link_visible'   => false,
            'timepasses_visible'      => false,
        );

        PostModule::of( $I )
            ->checkPost( $post_id );

        TimepassModule::of( $I )
            ->purchaseTimepass( $post_id );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->deactivatePlugin()
            ->deletePlugin()
            ->installPlugin( '0.9.11.4' )
            ->activatePlugin();

        AuthModule::of( $I )
            ->logout();

        PostModule::of( $I )
            ->checkPost( $post_id, null, $after_purchase_options );

        AuthModule::of( $I )
            ->login();

        SetupModule::of( $I )
            ->deactivatePlugin()
            ->deletePlugin();

        AuthModule::of( $I )
            ->logout();
    }
}
