Getting Started With DataGridBundle
===================================

[![Build Status](https://secure.travis-ci.org/AliHichem/AliDatatableBundle.png?branch=master)](http://travis-ci.org/AliHichem/AliDatatableBundle)

The Datatable bundle for symfony2 allow for easily integration of the [jQuery Datatable plugin](http://datatables.net/) with the doctrine2 entities.
This bundle provides a way to make a projection of a doctrine2 entity to a powerful jquery datagrid. It mainly includes:

 * datatable service container: to manage the datatable as a service.
 * twig extension: for view integration.
 * dynamic pager handler : no need to set your pager.
 * default action link builder: if activated, the bundle generates default edit/delete links. 

<div style="text-align:center"><img alt="Screenshot" src="https://github.com/AliHichem/AliDatatableBundle/raw/master/Resources/public/images/sample.png"></div>

**Compatibility**: latest successiful test with Symfony 2.0.7. Compatibility with higher version of symfony2 is not guaranteed.

**Limitations**: 

 * Do not support doctrine2 associations
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
                    ->setWhere(                                                     // set your dql where statement
                         'x.adress = :adress',
                         array('adress' => 'Paris') 
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
        $this->_datatable();                                                        // call the datatable config initializer
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

## Advenced use of datatable

### sql where statement

Assuming the example above, you can add your dql where statement

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
                    ->setWhere(                                                     // set your dql where statement
                         'x.adress = :adress',
                         array('adress' => 'Paris') 
                    )
                    ->setOrder("x.created", "desc")                                 // it's also possible to set the default order
                    ->setHasAction(true);                                           // you can disable action column from here by setting "false".
    }
    
    
### custom renderer

Assuming the example above, you can set your custom fields renderer using [PHP Closures](http://php.net/manual/en/class.closure.php).

    /**
     * set datatable configs
     * 
     * @return \Ali\DatatableBundle\Util\Datatable
     */
    private function _datatable()
    {
        $controller_instance = $this;
        return $this->get('datatable')
                    ->setEntity("XXXMyBundle:Entity", "x")                          // replace "XXXMyBundle:Entity" by your entity
                    ->setFields(
                            array(
                                "Name"          => 'x.name',                        // Declaration for fields: 
                                "Adress"        => 'x.adress',                      //      "label" => "alias.field_attribute_for_dql"
                                "_identifier_"  => 'x.id')                          // you have to put the identifier field without label. Do not replace the "_identifier_"
                            )
                    ->setRenderer(
                        function(&$data) use ($controller_instance)
                        {
                            foreach ($data as $key => $value)
                            {
                                if ($key == 1)                                      // 1 => adress field
                                {
                                    $data[$key] = $controller_instance
                                            ->get('templating')
                                            ->render(
                                                   'XXXMyBundle:Module:_grid_entity.html.twig', 
                                                   array('data' => $value)
                                            );
                                }
                            }
                        }
                    )
                    ->setOrder("x.created", "desc")                                 // it's also possible to set the default order
                    ->setHasAction(true);                                           // you can disable action column from here by setting "false".
    }

<div style="text-align:center"><img alt="Screenshot" src="https://github.com/AliHichem/AliDatatableBundle/raw/master/Resources/public/images/sample_02.png"></div>

**Future improvements**: 

 * add search
 * add support for doctrine associations
 

