<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
if($products){
    echo "<div class='store-product-list row'>";

    $i=1;
    foreach($products as $product){

        $optionGroups = $product->getProductOptionGroups();
        $optionItems = $product->getProductOptionItems(true);

        if ($product->hasVariations()) {
            $variations = StoreProductVariation::getVariationsForProduct($product);

            $variationLookup = array();

            if (!empty($variations)) {
                foreach ($variations as $variation) {
                    // returned pre-sorted
                    $ids = $variation->getOptionItemIDs();
                    $variationLookup[implode('_', $ids)] = $variation;
                }
            }
        }

        //this is done so we can get a type of active class if there's a product list on the product page
        if(Page::getCurrentPage()->getCollectionID()==$product->getProductPageID()){
            $activeclass =  'on-product-page';
        }

        $columnClass = 'col-md-12';

        if ($productsPerRow == 2) {
            $columnClass = 'col-md-6';
        }

        if ($productsPerRow == 3) {
            $columnClass = 'col-md-4';
        }

        if ($productsPerRow == 4) {
            $columnClass = 'col-md-3';
        }


    ?>
    
        <div class="store-product-list-item <?= $columnClass; ?> <?= $activeclass; ?>">
            <form   id="store-form-add-to-cart-list-<?= $product->getProductID()?>">
                <h2 class="store-product-list-name"><?= $product->getProductName()?></h2>
                <?php 
                    $imgObj = $product->getProductImageObj();
                    if(is_object($imgObj)){
                        $thumb = $ih->getThumbnail($imgObj,400,280,true);?>
                        <p class="store-product-list-thumbnail">
                            <?php if($showQuickViewLink){ ?>
                            <a class="store-product-quick-view" data-product-id="<?= $product->getProductID()?>" href="#">
                                <img src="<?= $thumb->src?>" class="img-responsive">
                            </a>
                            <?php } else { ?>
                            <img src="<?= $thumb->src?>" class="img-responsive">
                            <?php } ?>

                        </p>
                <?php
                    }// if is_obj
                ?>

                <p class="store-product-list-price">
                    <?php
                        $salePrice = $product->getProductSalePrice();
                        if(isset($salePrice) && $salePrice != ""){
                            echo '<span class="sale-price">'.$product->getFormattedSalePrice().'</span>';
                            echo ' ' . t('was') . ' ' . '<span class="original-price">'.$product->getFormattedOriginalPrice().'</span>';
                        } else {
                            echo $product->getFormattedPrice();
                        }
                    ?>
                </p>
                <?php if($showDescription){ ?>
                <div class="store-product-list-description"><?= $product->getProductDesc()?></div>
                <?php } ?>
                <?php if($showPageLink){?>
                <p><a href="<?= URL::page(Page::getByID($product->getProductPageID()))?>" class="store-btn-more-details btn btn-default"><?= t("More Details")?></a></p>
                <?php } ?>
                <?php if($showAddToCart){

                foreach($optionGroups as $optionGroup) {
                    $groupoptions = array();
                    foreach ($optionItems as $option) {
                        if ($option->getProductOptionGroupID() == $optionGroup->getID()) {
                            $groupoptions[] = $option;
                        }
                    }
                    ?>
                    <?php if (!empty($groupoptions)) { ?>
                        <div class="store-product-option-group form-group">
                            <label class="store-option-group-label"><?= $optionGroup->getName() ?></label>
                            <select class="form-control" name="pog<?= $optionGroup->getID() ?>">
                                <?php
                                foreach ($groupoptions as $option) { ?>
                                    <option value="<?= $option->getID() ?>"><?= $option->getName() ?></option>
                                    <?php
                                    // below is an example of a radio button, comment out the <select> and <option> tags to use instead
                                    //echo '<input type="radio" name="pog'.$optionGroup->getID().'" value="'. $option->getID(). '" />' . $option->getName() . '<br />'; ?>
                                <?php } ?>
                            </select>
                        </div>
                    <?php }
                }?>

                <input type="hidden" name="pID" value="<?= $product->getProductID()?>">
                <input type="hidden" name="quantity" class="store-product-qty" value="1">
                <p><a href="#" data-add-type="list" data-product-id="<?= $product->getProductID()?>" class="store-btn-add-to-cart btn btn-primary <?= ($product->isSellable() ? '' : 'hidden');?> "><?=  ($btnText ? h($btnText) : t("Add to Cart"))?></a></p>
                <p class="store-out-of-stock-label alert alert-warning <?= ($product->isSellable() ? 'hidden' : '');?>"><?= t("Out of Stock")?></p>

                <?php } ?>

            </form><!-- .product-list-item-inner -->
        </div><!-- .product-list-item -->


        <?php if ($product->hasVariations() && !empty($variationLookup)) {?>
            <script>
                $(function() {
                    <?php
                    $varationData = array();
                    foreach($variationLookup as $key=>$variation) {
                        $product->setVariation($variation);

                        $imgObj = $product->getProductImageObj();

                        if ($imgObj) {
                            $thumb = Core::make('helper/image')->getThumbnail($imgObj,400,280,true);
                        }

                        $varationData[$key] = array(
                        'price'=>$product->getFormattedOriginalPrice(),
                        'saleprice'=>$product->getFormattedSalePrice(),
                        'available'=>($variation->isSellable()),
                        'imageThumb'=>$thumb ? $thumb->src : '',
                        'image'=>$imgObj ? $imgObj->getRelativePath() : '');

                    } ?>


                    $('#store-form-add-to-cart-list-<?= $product->getProductID()?> select').change(function(){
                        var variationdata = <?= json_encode($varationData); ?>;
                        var ar = [];

                        $('#store-form-add-to-cart-list-<?= $product->getProductID()?> select').each(function(){
                            ar.push($(this).val());
                        })

                        ar.sort();

                        var pli = $(this).closest('.store-product-list-item');

                        if (variationdata[ar.join('_')]['saleprice']) {
                            var pricing =  '<span class="store-sale-price">'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                               ' <?= t('was');?> ' + '<span class="store-original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                            pli.find('.store-product-list-price').html(pricing);

                        } else {
                            pli.find('.store-product-list-price').html(variationdata[ar.join('_')]['price']);
                        }

                        if (variationdata[ar.join('_')]['available']) {
                            pli.find('.store-out-of-stock-label').addClass('hidden');
                            pli.find('.store-btn-add-to-cart').removeClass('hidden');
                        } else {
                            pli.find('.store-out-of-stock-label').removeClass('hidden');
                            pli.find('.store-btn-add-to-cart').addClass('hidden');
                        }

                        if (variationdata[ar.join('_')]['imageThumb']) {
                            var image = pli.find('.store-product-list-thumbnail img');

                            if (image) {
                                image.attr('src', variationdata[ar.join('_')]['imageThumb']);

                            }
                        }

                    });
                });
            </script>
        <?php } ?>
        
        <?php 
            if($i%$productsPerRow==0){
                echo "</div>";
                echo "<div class='store-product-list row'>";
            }
        
        $i++;
    
    }// foreach    
    echo "</div><!-- .product-list -->";
    
    if($showPagination){
        if ($paginator->getTotalPages() > 1) {
            echo '<div class="row">';
            echo $pagination;
            echo '</div>';
        }
    }
    
} //if products
else { ?>
    <div class="alert alert-info"><?= t("No Products Available")?></div>
<?php } ?>