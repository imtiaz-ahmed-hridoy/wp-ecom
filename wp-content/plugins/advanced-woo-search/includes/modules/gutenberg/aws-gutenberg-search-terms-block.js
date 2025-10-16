( function( blocks, element, serverSideRender, blockEditor, components ) {

    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var RichText = blockEditor.RichText;
    var ServerSideRender = serverSideRender;

    var InspectorControls = blockEditor.InspectorControls;
    var Fragment = element.Fragment;

    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var NumberControl = components.__experimentalNumberControl;
    var Panel = components.Panel;
    var PanelBody = components.PanelBody;
    var PanelRow = components.PanelRow;

    var sIcon = el('svg', {
            width: 20,
            height: 20,
            viewBox: "0 0 1000 1004.1441",
            xmlns: "http://www.w3.org/2000/svg",
            style: { fill: "#7f54b3" }
        },
        el('path', {
            d: "m479 179.144c0-7 9-12 21-12s21 5 21 12v100c0 7-9 13-21 13s-21-6-21-13zm-208 92c0 11-9 21-21 21s-21-10-21-21v-42c0-34 28-62 63-62h416c35 0 63 28 63 62v42c0 11-9 21-21 21s-21-10-21-21v-42c0-12-9-21-21-21h-416c-12 0-21 9-21 21zm-83 21v41h125v-41zm-30-42h184c7 0 12 5 12 12v100c0 8-5 13-12 13h-184c-7 0-12-5-12-13v-100c0-7 5-12 12-12zm280 42v41h125v-41zm-30-42h184c7 0 12 5 12 12v100c0 8-5 13-12 13h-184c-7 0-12-5-12-13v-100c0-7 5-12 12-12zm280 42v41h125v-41zm-30-42h184c7 0 12 5 12 12v100c0 8-5 13-12 13h-184c-7 0-12-5-12-13v-100c0-7 5-12 12-12zm-168 500c12 0 21 9 21 21s-9 21-21 21h-390c-54 0-100-37-100-86v-579c0-48 46-85 100-85h758c55 0 100 37 100 85v580c0 33-23 63-57 77-7 3-15 5-22 6-3 0-6 1-9 1-5 1-9 1-11 1-12 0-21-8-22-20 0-12 8-21 20-22h15c5-1 9-3 14-5 19-8 31-23 31-38v-580c0-23-25-44-59-44h-758c-33 0-58 21-58 44v580c0 23 25 43 58 43zm122 125h126l46-147h-216zm183-188c19 0 34 15 34 33 0 3-1 7-2 10l-51 164c-4 14-17 23-31 23h-139c-15 0-28-9-32-24l-54-179v-1c-4-11-9-22-16-32-5-7-10-10-17-10-12 0-21-9-21-21s9-20 21-20c21 0 39 9 51 27 7 10 12 20 16 30zm-173 301c-17 0-29-13-29-30s12-29 29-29 29 13 29 29-13 30-29 30zm104 0c-16 0-29-13-29-30s13-29 29-29 29 13 29 29-13 30-29 30z"
        })
    );

    blocks.updateCategory('aws');

    var blockStyle = {
        backgroundColor: '#900',
        color: '#fff',
        padding: '20px',
    };

    blocks.registerBlockType( 'advanced-woo-search/search-terms-block', {
        apiVersion: 2,
        title: 'Taxonomies Results',
        description: 'Advanced Woo Search taxonomies search results display. Works only inside WooCommerce search results page.',
        icon: sIcon,
        category: 'aws',
        example: {
            attributes: {
                limit: 3,
                columns: 3,
                taxonomies_val: 'product_cat',
            },
        },
        edit: function( props ) {

            var blockProps = blockEditor.hasOwnProperty('useBlockProps') ? blockEditor.useBlockProps() : null;

            return (
                el( Fragment, {},
                    el( InspectorControls, {},
                        el( PanelBody, { title: 'Content Settings', initialOpen: true },


                            el( PanelRow, {},
                                el( NumberControl,
                                    {
                                        label: 'Results Count',
                                        onChange: ( value ) => {
                                            props.setAttributes( { limit: parseInt(value) } );
                                        },
                                        value: props.attributes.limit
                                    }
                                ),
                            ),

                            el( PanelRow, {},
                                el( NumberControl,
                                    {
                                        label: 'Columns',
                                        onChange: ( value ) => {
                                            props.setAttributes( { columns: parseInt(value) } );
                                        },
                                        value: props.attributes.columns
                                    }
                                ),
                            ),
                            
                            el( PanelRow, {},
                                el( SelectControl,
                                    {
                                        label: 'Taxonomy',
                                        options : props.attributes.taxonomies,
                                        onChange: ( value ) => {
                                            props.setAttributes( { taxonomies_val: value } );
                                        },
                                        value: props.attributes.taxonomies_val,
                                        __nextHasNoMarginBottom: true,
                                        style: { 'minWidth': '120px' }
                                    }
                                ),
                            ),

                        ),

                    ),
                    el(
                        'div',
                        blockProps,
                        el( ServerSideRender, {
                            block: 'advanced-woo-search/search-terms-block',
                            attributes: props.attributes,
                        } )
                    )
                )

            );


        },
        save: function( props ) {
            return null;
        },
    } );
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.serverSideRender,
    window.wp.blockEditor,
    window.wp.components,
) );