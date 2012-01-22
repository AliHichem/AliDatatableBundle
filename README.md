Getting Started With DataGridBundle
===================================

The Datatable bundle for symfony2 allow for easily integration of the [jQuery Datatable plugin](http://datatables.net/) with the doctrine2 entities.
This bundle provides a way to make a projection of a doctrine2 entity to a powerful jquery datagrid. It mainly includes:

 * datatable service container: to manage the datatable as a service.
 * twig extension: for view integration.
 * dynamic pager handler : no need to set your pager.
 * default action link builder: if activated, the bundle generates default edit/delete links. 

<img src="https://github.com/AliHichem/AliDatatableBundle/raw/master/Resources/public/images/sample.png" alt="Screenshot" />

**Compatibility**: latest successiful test with Symfony 2.0.7. Compatibility with higher version of symfony2 is not guaranteed.

**Limitations**: 

 * Do not support doctrine2 associations
 * No sorting available
 * No search available
 
Installation
------------

include the source to your deps files

    [AliDatatableBundle]
        git=git://github.com/AliHichem/AliDatatableBundle
        target=bundles/Ali/DatatableBundle

install the bundle

    $ bin/vendor install

register the bundle
    
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Ali\DatatableBundle\AliDatatableBundle(),
        );

add the namespace to the autoloader

    $loader->registerNamespaces(array(
        ...
        'Ali'              => __DIR__.'/../vendor/bundles',
    ));

include the service file to your config.yml

    imports:
    - { resource: "@AliDatatableBundle/Resources/config/services.yml" }

generate the assets symlinks

    $ app/console assets:install --symlink web

How to use
----------

Assuming for example that you need a grid in your "index" action, create in your controller method as below:

    /**
     * set datatable configs
     * 
     * @return \Ali\DatatableBundle\Util\Datatable
     */
    private function _datatable()
    {
        return $this->get('datatable')
                    ->setEntity("XXXMyBundle:Entity", "x")                          // replace "XXXMyBundle:Entity" by your entity
                    ->setFields(
                            array(
                                "Name"          => 'x.name',                        // Declaration for fields: 
                                "Adress"        => 'x.adress',                      //      "label" => "alias.field_attribute_for_dql"
                                "_identifier_"  => 'x.id')                          // you have to put the identifier field without label. Do not replace the "_identifier_"
                            )
                    ->setOrder("x.created", "desc")                                 // it's also possible to set the default order
                    ->setHasAction(true);                                           // you can disable action column from here by setting "false".
    }
    
    
    /**
     * Grid action
     * @return Response
     */
    public function gridAction()
    {   
        return $this->_datatable()->execute();                                      // call the "execute" method in your grid action
    }
    
    /**
     * Lists all entities.
     * @return Response
     */
    public function indexAction()
    {
        $datatable = $this->_datatable();                                           // call the datatable config initializer
        return $this->render('XXXMyBundle:Module:index.html.twig');                 // replace "XXXMyBundle:Module:index.html.twig" by yours
    }


## Rendering inside twig

    <!-- XXX\MyBundle\Resources\views\Module\index.html.twig -->
    
    <!-- include the assets -->
    <link href="{{ asset('bundles/alidatatable/css/demo_table.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('bundles/alidatatable/css/smoothness/jquery-ui-1.8.4.custom.css') }}" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="{{ asset('bundles/alidatatable/js/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('bundles/alidatatable/js/jquery.datatable.inc.js') }}"></script>
    <script type="text/javascript" src="{{ asset('bundles/alidatatable/js/jquery.dataTables.min.js') }}"></script>    
    
    {{ datatable({ 
            'id' : 'dta-unique-id',
            'edit_route' : 'RouteForYourEntity_edit',
            'delete_route' : 'RouteForYourEntity_delete',
            'js' : {
                'sAjaxSource' : path('RouteForYour_grid_action')
            }
        }) | raw
    }}

**Future improvements**: 

 * add sorting
 * add search
 * add support for doctrine associations
 

