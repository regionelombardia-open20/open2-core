<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    retecomuni\frontend\views\site\parts
 * @category   CategoryName
 */

use open20\design\assets\BootstrapItaliaDesignAsset;

$bootstrapItaliaAsset = BootstrapItaliaDesignAsset::register($this);

?>

<div class="d-inline-flex">
            
    <!-- condividi -->
    <div class="dropdown">
        <a class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLinkCondividi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

            <svg class="icon icon-sm icon-primary mr-1">
                <use xlink:href="<?= $bootstrapItaliaAsset->baseUrl ?>/sprite/material-sprite.svg#share-variant">
                </use>
            </svg>

            Condividi
           
        </a>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuLinkCondividi">
            <div class="link-list-wrapper">
                <ul class="link-list">
                    <?php if( isset($sharing_link) ) : ?>
                            
                        <?php if( isset($sharing_link['facebook']) && !empty($sharing_link['facebook']) ) : ?>
                            <li>

                                <a class="list-item" href="<?= $sharing_link['facebook'] ?>" target="_blank">
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-facebook"></use>
                                    </svg>
                                    <span>
                                        Facebook
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if( isset($sharing_link['twitter']) && !empty($sharing_link['twitter']) ) : ?>
                            <li>

                                <a class="list-item" href="<?= $sharing_link['twitter'] ?>" target="_blank">
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-twitter"></use>

                                    </svg>
                                    <span>
                                        Twitter
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if( isset($sharing_link['linkedin']) && !empty($sharing_link['linkedin']) ) : ?>
                            <li>

                                <a class="list-item" href="<?= $sharing_link['linkedin'] ?>" target="_blank">
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-linkedin"></use>
                                    </svg>
                                    <span>
                                        Linkedin
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if( isset($sharing_link['whatsapp']) && !empty($sharing_link['whatsapp']) ) : ?>
                            <li>

                                <a class="list-item" href="<?= $sharing_link['whatsapp'] ?>" target="_blank">
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-whatsapp"></use>
                                    </svg>
                                    <span>
                                        WhatsApp
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if( isset($sharing_link['telegram']) && !empty($sharing_link['telegram']) ) : ?>
                            <li>

                                <a class="list-item" href="<?= $sharing_link['telegram'] ?>" target="_blank">
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/sprite/material-sprite.svg#telegram"></use>
                                    </svg>
                                    <span>
                                        Telegram
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- vedi azioni  -->
    <div class="dropdown">
        <a class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLinkVediAzioni" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            
            <svg class="icon icon-sm icon-primary mr-1">

                <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/sprite/material-sprite.svg#dots-vertical">
                </use>
            </svg>
            Vedi Azioni

        </a>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuLinkVediAzioni">
            <div class="link-list-wrapper">
                <ul class="link-list">
                    <?php if( isset($actions_link) ) : ?>

                        <?php if( isset($actions_link['download'])) : ?>
                            <li>
                                <a class="list-item" href="<?= $actions_link['download'] ?>" download>
                                    <svg class="icon icon-sm icon-primary mr-1">

                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-download"></use>

                                    </svg>
                                    <span>
                                        Scarica
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if( isset($actions_link['print']) && !empty($actions_link['print']) ) : ?>
                            <li>

                                <a class="list-item" href="#" onclick="<?= $actions_link['print'] ?>" >
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-print"></use>

                                    </svg>
                                    <span>
                                        Stampa
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if( isset($actions_link['send']) && !empty($actions_link['send']) ) : ?>
                            <li>

                                <a class="list-item" href="<?= $actions_link['send'] ?>">
                                    <svg class="icon icon-sm icon-primary mr-1">
                                        <use xlink:href="<?=$bootstrapItaliaAsset->baseUrl?>/node_modules/bootstrap-italia/dist/svg/sprite.svg#it-mail"></use>

                                    </svg>
                                    <span>
                                        Invia
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div>
