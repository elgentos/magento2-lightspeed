# Magento 2 - Lightspeed optimizations

Process your Lightspeed feedback with ease. This module defines several sections where you can define
common feedback from Google Lightspeed.

## Installation

Installation is easy, in your Magento 2 project root.

```bash
composer require elgentos/module-lightspeed
```

## Features

### Javascript handling
move all `script` tags before body end. This is the default after installing this module, no exceptions(TODO).

### Connection optimization (layout.xml)
Allow modern browsers to use dns prefetching and preconnecting.

DNS prefetching only does a dns lookup, preconnecting already connects to the remote server and does ssl handshake.
Preconnecting is limited to a few connections which is defined in your browser, we have fallback to dns-prefetching,
but think before you add everything to `preconnect` 

### Fonts (layout.xml)
Load external fonts to the head section

### Styles (layout.xml)
Inline css in the head or before body end for critical css.

### External CSS (layout.xml)
We have several options for optimizing external css;
* directly in the head
* before body end
* defer till all other stuff is done. 

### Javascript (layout.xml)
Javascript via XML before body end via layout xml.

## Usage

Easiest usage to keep everything together is to add a handle to `layout/default.xml`.
You can also add controller specific rules, add them in the controller specific handles, for instance `layout/catalog_category_default.xml`

You can also add specific rules and bind them to your module, instead to the theme.

`app/design/frontend/your/theme/Magento_Theme/layout/default.xml`
```xml
<?xml version="1.0"?>
<page layout="1column" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <update handle="default_lightspeed" />

    <!-- .... snap ... -->

</page>
``` 

After that all default lightspeed feedback can go into `layout/default_lightspeed.xml`
`app/design/frontend/your/theme/Magento_Theme/layout/default.xml`
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">

    <body>
        <referenceBlock name="lightspeed.head.dns-prefetch">
            <!-- Use addItems to add multiple values without writing to much code -->
            <action method="addItems">
                <argument name="items" xsi:type="array">
                    <item name="google" xsi:type="string">www.google.com</item>
                    <item name="google-static" xsi:type="string">www.gstatic.com</item>
                    <item name="google-adservices" xsi:type="string">www.googleadservices.com</item>
                    <!-- .... etc ... -->
                </argument>
            </action>
        </referenceBlock>
    
        <referenceBlock name="lightspeed.head.preconnect">
            <action method="addItems">
                <argument name="items" xsi:type="array">
                    <item name="google-apis" xsi:type="string">fonts.googleapis.com</item>
                    <item name="google-fonts" xsi:type="string">fonts.gstatic.com</item>
    
                    <!-- .... etc ... -->
                    
                    <item name="google-gtm" xsi:type="string">www.googletagmanager.com</item>
                </argument>
            </action>
        </referenceBlock>

        <referenceBlock name="lightspeed.head.fonts">
            <!-- Use addItem if you just need to add one line -->
            <action method="addItem">
                <argument name="value" xsi:type="string">https://fonts.googleapis.com/css?family=Font&amp;amp;display=swap</argument>
            </action>
            <!-- .... etc ... -->
        </referenceBlock>

        <!-- Choose if you want your css to go in the head, or in the footer -->
        <referenceBlock name="lightspeed.head.inline-styles">
            <action method="addItem">
                <argument name="value" xsi:type="string"><![CDATA[*{color: red !important;}]]></argument>
            </action>
        </referenceBlock>
        <referenceBlock name="lightspeed.body.inline-styles">
            <action method="addItem">
                <argument name="value" xsi:type="string"><![CDATA[*{color: red !important;}]]></argument>
            </action>
        </referenceBlock>

        <referenceBlock name="lightspeed.body.defer-styles">
            <action method="addItem">
                <!-- Use a custom helper if you need to add some logic outside of layout.xml, needs to return a string -->
                <argument name="value" xsi:type="helper" helper="Magento\Helper\Data::getStyleSheet" />
            </action>
        </referenceBlock>
        <!-- Use defer styles which waits till all js/css painting is done -->
        <referenceBlock name="lightspeed.body.no-defer-styles">
            <action method="addItem">
                <argument name="value" xsi:type="helper" helper="Magento\Helper\Data::getStyleSheet" />
            </action>
        </referenceBlock>

    </body>
</page>
``` 

## Block quick references

### References HEAD

* `lightspeed.head.dns-prefetch`
* `lightspeed.head.preconnect`
* `lightspeed.head.fonts`
* `lightspeed.head.inline-styles`

### References (before body end)

* `lightspeed.body.defer-styles`
* `lightspeed.body.no-defer-styles`
* `lightspeed.body.inline-styles`
* `lightspeed.body.footer-js`

## Block code reference

You can also use `\Elgentos\Lightspeed\Block\ItemsWithPattern` to add your own references.

Public:
* `addItem(string $value): void`
* `addItems(array $values): void`
* `getItems(): array`
* `hasItems(): bool`
* `removeItem(string $value): void`
* `setPattern(string $pattern): void`
* `render(): string`

### Custom block definition

`default.xml`
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <!-- .... snap ... -->
    <body>
        <!-- .... example custom ... -->
       <block name="lightspeed.head.custom" class="Elgentos\Lightspeed\Block\ItemsWithPattern">
           <arguments>
               <argument name="pattern" xsi:type="string"><![CDATA[<link href="//%s" rel="dns-prefetch" />]]></argument>
           </arguments>
       </block>

        <!-- Move element to correct section -->
        <move element="lightspeed.head.custom" destination="head.additional" />
        <!-- or to the footer -->
        <move element="lightspeed.head.custom" destination="before.body.end" />
    </body>
</page>
```


## Authors
* [Gideon Overeem](@govereem)
* [Jeroen Boersma](@Jeroen_Boersma)
