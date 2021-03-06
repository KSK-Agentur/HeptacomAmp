{extends file="frontend/plugins/heptacom_amp/listing/product-box/box-basic.tpl"}

{namespace name="frontend/listing/box_article"}

{block name='frontend_listing_box_article_description'}{/block}

{block name='frontend_listing_box_article_picture'}
    <a href="{$sArticle.linkDetails}"
       title="{$sArticle.articleName|escape}"
       class="product--image">
        {block name='frontend_listing_box_article_image_element'}
            <span class="image--element">
            {block name='frontend_listing_box_article_image_media'}
                <span class="image--media">

                    {$desc = $sArticle.articleName|escape}

                    {if isset($sArticle.image.thumbnails)}

                        {if $sArticle.image.description}
                            {$desc = $sArticle.image.description|escape}
                        {/if}

                        {block name='frontend_listing_box_article_image_picture_element'}
                            <amp-img layout="fill"
                                     src="{$sArticle.image.thumbnails[1].source}"
                                     srcset="{$sArticle.image.thumbnails[1].sourceSet}"
                                     alt="{$desc}"
                                     title="{$desc|truncate:160}"
                            ></amp-img>
                        {/block}
                    {else}
                        <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                             alt="{$desc}"
                             title="{$desc|truncate:160}" />
                        <amp-img layout="fill"
                                 src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                                 alt="{$desc}"
                                 title="{$desc|truncate:160}"
                        ></amp-img>
                    {/if}
                </span>
            {/block}
        </span>
        {/block}
    </a>
{/block}
