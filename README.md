AliDatatableBundle
==================

[![Build Status](https://secure.travis-ci.org/AliHichem/AliDatatableBundle.png?branch=master)](http://travis-ci.org/AliHichem/AliDatatableBundle)

The Datatable bundle for symfony2 allow for easily integration of the [jQuery Datatable plugin](http://datatables.net/) with the doctrine2 entities.
This bundle provides a way to make a projection of a doctrine2 entity to a powerful jquery datagrid. It mainly includes:

 * datatable service container: to manage the datatable as a service.
 * twig extension: for view integration.
 * dynamic pager handler : no need to set your pager.
 * default action link builder: if activated, the bundle generates default edit/delete links. 
 * support doctrine2 association.
 * support of doctrine query builder.
 * support of column search.
 * support of custom twig/phpClosure renderers.
 * support of custom grouped actions.
 
###### Soon : support of ODM (MongoDB) : developement under progress in the "mongodb" branch.

<div style="text-align:center"><img alt="Screenshot" src="https://github.com/AliHichem/AliDatatableBundle/raw/master/Resources/public/images/sample_01.png"></div>

-------------------------------------
##### [Installation](#installation-1) 

1. [Download AliDatatableBundle using composer](#step-1-download-alidatatablebundle)
2. [Enable the Bundle](#step-2--enable-the-bundle)
3. [Configure your application's config.yml](#step-3--activate-the-main-configs)

##### [How to use AliDatatableBundle ?](#-how-to-use-alidatatablebundle-)
##### [Rendering inside Twig](#-rendering-inside-twig)
##### [Advanced php config](#-advanced-php-config)
##### [Use of search filters](#-use-of-search-filters)

*  [Activate search globally](#activate-search-globally)
*  [Set search fields](#set-search-fields) (new) 

##### [Multiple actions](#-multiple-actions) (new) 
##### [Custom renderer](#-custom-renderer)
##### [Translation](#-translation)
##### [Multiple datatable in the same view](#-multiple-datatable-in-the-same-view)

---------------------------------------

### Installation

Installation is a quick (I promise!) 3 step process:

1. [Download AliDatatableBundle using composer](#step-1-download-alidatatablebundle)
2. [Enable the Bundle](#step-2--enable-the-bundle)
3. [Configure your application's config.yml](#step-3--activate-the-main-configs)

##### Step 1: Download AliDatatableBundle 

###### Using composer (Symfony > 2.0)

Add datatable bundle in your composer.json as below:

```js
"require": {
    ...
    "ali/datatable": "dev-master"
}
```

Update/install with this command:

```
php composer.phar update ali/datatable
```

###### Using native symfony2 installer (Symfony < 2.1) : support of SF2 v < 2.1 will be removed soon.

Include the source to your deps files

```
[AliDatatableBundle]
    git=git://github.com/AliHichem/AliDatatableBundle
    target=bundles/Ali/DatatableBundle
```

install the bundle

```
$ bin/vendor install
```

##### Step 2:  Enable the bundle

register the bundle

```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Ali\DatatableBundle\AliDatatableBundle(),
);
```

(only for symfony < 2.1 )
add the namespace to the autoloader

```php
$loader->registerNamespaces(array(
    ...
    'Ali'              => __DIR__.'/../vendor/bundles',
));
```

generate the assets symlinks

```
$ app/console assets:install --symlink web
```

##### Step 3:  Activate the main configs

in this section you can put the global config that you want to set for all the instance of datatable in your project.

###### To keep it to default 

```
# app/config/config.yml
ali_datatable:  
    all:    ~
    js:     ~
```

the "js" config will be applied to datatable exactly like you do with "$().datatable({ you config });" , you can even put javascript code.
Note: all you js config have to string typed, make sure to use (") as delimiters.

###### Config sample 

```
ali_datatable:  
    all: 
        action:           true
        search:           false
    js:  
        iDisplayLength: "10"
        aLengthMenu: "[[5,10, 25, 50, -1], [5,10, 25, 50, 'All']]"
        bJQueryUI: "false"
        fnPreDrawCallback: |
            function( e ) {
                // you custom code goes here
            }
```

### # How to use AliDatatableBundle ?

Assuming for example that you need a grid in your "index" action, create in your controller method as below:

```php
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
                            "Address"        => 'x.address',                    //      "label" => "alias.field_attribute_for_dql"
                            "_identifier_"  => 'x.id')                          // you have to put the identifier field without label. Do not replace the "_identifier_"
                        )
                ->setWhere(                                                     // set your dql where statement
                     'x.address = :address',
                     array('address' => 'Paris') 
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
```

### # Rendering inside Twig

```js
<!-- XXX\MyBundle\Resources\views\Module\index.html.twig -->

<!-- include the assets -->
<link href="{{ asset('bundles/alidatatable/css/demo_table.css') }}" type="text/css" rel="stylesheet" />
<link href="{{ asset('bundles/alidatatable/css/smoothness/jquery-ui-1.8.4.custom.css') }}" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="{{ asset('bundles/alidatatable/js/jquery.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/alidatatable/js/jquery.datatable.inc.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/alidatatable/js/jquery.dataTables.min.js') }}"></script>    

{{ datatable({ 
        'edit_route' : 'RouteForYourEntity_edit',
        'delete_route' : 'RouteForYourEntity_delete',
        'js' : {
            'sAjaxSource' : path('RouteForYour_grid_action')
        }
    })
}}
```


Advanced Use of datatable
-------------------------

### # Advanced php config

Assuming the example above, you can add your joins and where statements

```php
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
                            "Address"        => 'x.address',                    //      "label" => "alias.field_attribute_for_dql"
                            "Group"         => 'g.name',
                            "Team"          => 't.name',
                            "_identifier_"  => 'x.id')                          // you have to put the identifier field without label. Do not replace the "_identifier_"
                        )
                ->addJoin('x.group', 'g', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN)
                ->addJoin('x.team', 't', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN)
                ->setWhere(                                                     // set your dql where statement
                     'x.address = :address',
                     array('address' => 'Paris') 
                )
                ->setOrder("x.created", "desc")                                 // it's also possible to set the default order
                ->setHasAction(true);                                           // you can disable action column from here by setting "false".
}
```

### # Use of search filters


*  [Activate search globally](#activate-search-globally)
*  [Set search fields](#set-search-fields)

###### Activate search globally

The filtering functionality that is very useful for quickly search through the information from the database  - however the search is only built in one way : the individual column filtering.

By default the filtering functionality is disabled, to get it working you just need to activate it from your configuration method like this :

```php
private function _datatable()
{
    return $this->get('datatable')
                //...
                ->setSearch(TRUE);
}
```
###### Set search fields

You can set fields where you want to enable your search , by default search wont be active for actions column but you might want to disable search for other columns.
Let say you want search to be active only for "field1" and "field3", you just need to activate search for the approriate column key and your datatable config should be : 

```php
/**
 * set datatable configs
 * 
 * @return \Ali\DatatableBundle\Util\Datatable
 */
private function _datatable()
{
    $datatable = $this->get('datatable');
    return $datatable->setEntity("XXXMyBundle:Entity", "x")
                    ->setFields(
                            array(
                                "label of field1" => 'x.field1',   // column key 0
                                "label of field2" => 'x.field2',   // column key 1
                                "label of field3" => 'x.field3',   // column key 2
                                "_identifier_" => 'x.id')          // column key 3
                    )
                    ->setSearch(true)
                    ->setSearchFields(array(0,2))
    ;
}
```

### # Multiple actions

Sometimes, it's good to be able to do the same action on multiple records like deleting, activating, moving ...
Well this is very easy to add to your datatable: all what you need is to declare your multiple action as follow


```php
/**
 * set datatable configs
 * 
 * @return \Ali\DatatableBundle\Util\Datatable
 */
private function _datatable()
{
    $datatable = $this->get('datatable');
    return $datatable->setEntity("XXXMyBundle:Entity", "x")
                    ->setFields(
                            array(
                                "label of field1" => 'x.field1',   // column key 0
                                "label of field2" => 'x.field2',   // column key 1
                                "_identifier_" => 'x.id')          // column key 2
                    )
                    ->setMultiple(
                                array(
                                    'delete' => array(
                                        'title' => 'Delete',
                                        'route' => 'multiple_delete_route' // path to multiple delete route
                                    )
                                )
                        )
    ;
}
```

Then all what you have to do is to add the necessary logic in your "multiple_delete_route" (or whatever your route is for). 
In that action , you can get the selected ids by :

```php
$data = $this->getRequest()->get('dataTables');
$ids  = $data['actions'];
```

### # Custom renderer

**Twig renderers**

To set your own column structure, you can use a custom twig renderer as below: In this example you can find how to set the use of the default twig renderer for action fields which you can override as your own needs.

```php
/**
 * set datatable configs
 * 
 * @return \Ali\DatatableBundle\Util\Datatable
 */
private function _datatable()
{
    $datatable = $this->get('datatable');
    return $datatable->setEntity("XXXMyBundle:Entity", "x")
                    ->setFields(
                            array(
                                "label of field1" => 'x.field1',
                                "label of field2" => 'x.field2',
                                "_identifier_" => 'x.id')
                    )
                    ->setRenderers(
                            array(
                                2 => array(
                                    'view' => 'AliDatatableBundle:Renderers:_actions.html.twig',
                                    'params' => array(
                                            'edit_route'    => 'route_edit',
                                            'delete_route'  => 'route_delete',
                                            'delete_form_prototype'   => $datatable->getPrototype('delete_form')
                                        ),
                                ),
                            )
                    )
                    ->setHasAction(true);
}
```

In a twig renderer you can have access the the field value using dt_item variable
```
{{ dt_item }}
```
or access the entire entity object using dt_obj variable
```
<a href="{{ path('route_to_user_edit',{ 'user_id' : dt_obj.id }) }}" > {{ dt_obj.username }} </a>
```
NOTE: be careful of LAZY LOADING when using dt_obj !

**PHP Closures**

Assuming the example above, you can set your custom fields renderer using [PHP Closures](http://php.net/manual/en/class.closure.php).

```php
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
                            "Address"        => 'x.address',                    //      "label" => "alias.field_attribute_for_dql"
                            "_identifier_"  => 'x.id')                          // you have to put the identifier field without label. Do not replace the "_identifier_"
                        )
                ->setRenderer(
                    function(&$data) use ($controller_instance)
                    {
                        foreach ($data as $key => $value)
                        {
                            if ($key == 1)                                      // 1 => address field
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
```

<div style="text-align:center"><img alt="Screenshot" src="https://github.com/AliHichem/AliDatatableBundle/raw/master/Resources/public/images/sample_02.png"></div>

### # Translation

You can set your own translated labels by adding in your translation catalog entries as below:

```
ali:
    common:
        action: Actions
        confirm_delete: 'Are you sure to delete this item ?'
        delete: delete
        edit: edit
        no_action: "(can't remove)"
        sProcessing: "Processing..."
        sLengthMenu: "Show _MENU_ entries"
        sZeroRecords: "No matching records found"
        sInfo: "Showing _START_ to _END_ of _TOTAL_ entries"
        sInfoEmpty: "Showing 0 to 0 of 0 entries"
        sInfoFiltered: "(filtered from _MAX_ total entries)"
        sInfoPostFix: ""
        sSearch: "Search:"
        sLoadingRecords: ""
        sFirst: "First"
        sPrevious: "Previous"  
        sNext: "Next"
        sLast: "Last"
        search: "Search"
```
   
         
This bundle includes nine translation catalogs: Arabic, Chinese, Dutch, English, Spanish, French, Italian, Russian and Turkish
To get more translated entries, you can follow the [official datatable translation](http://datatables.net/plug-ins/i18n#English)

### # Doctrine query builder

To use your own query object to supply to the datatable object, you can perform this action using your proper "doctrine query object": AliDatatableBundle allow (since tag 1.2.0) to manipulate the query object provider which is now a doctrine query builder object, you can use it to update the query in all its components except of course in the selected field part. 

This is a classic config before using the doctrine query builder:

```php
private function _datatable()
{
    $datatable = $this->get('datatable')
                ->setEntity("XXXBundle:Entity", "e")
                ->setFields(
                        array(
                            "column1 label" => 'e.column1',
                            "_identifier_" => 'e.id')
                        )
                ->setWhere(
                    'e.column1 = :column1',
                    array('column1' => '1' )
                )
                ->setOrder("e.created", "desc");

     $qb = $datatable->getQueryBuilder()->getDoctrineQueryBuilder(); 
     // This is the doctrine query builder object , you can 
     // retrieve it and include your own change 

     return $datatable;
}
```

This is a config that uses a doctrine query object a query builder :

```php
private function _datatable()
{
    $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder();
    $qb->from("XXXBundle:Entity", "e")
       ->where('e.column1 = :column1')
       ->setParameters(array('column1' = 0))
       ->orderBy("e.created", "desc");

    $datatable = $this->get('datatable')
                ->setFields(
                        array(
                            "Column 1 label" => 'e.column1',
                            "_identifier_" => 'e.id')
                        );

    $datatable->getQueryBuilder()->setDoctrineQueryBuilder($qb);

    return $datatable;
}
```

### # Multiple datatable in the same view

To declare multiple datatables in the same view, you have to set the datatable identifier in you controller with "setDatatableId": Each of your databale config methods ( _datatable() , _datatable_1() .. _datatable_n() ) needs to set the same identifier used in your view:

**In the controller**


```php
protected function _datatable() 
{
    // ...
    return $this->get('datatable')
                ->setDatatableId('dta-unique-id_1')
                ->setEntity("XXXMyBundle:Entity", "x")
    // ...
}

protected function _datatableSecond() 
{
    // ...
    return $this->get('datatable')
                ->setDatatableId('dta-unique-id_2')
                ->setEntity("YYYMyBundle:Entity", "y")
    // ...
}
```

**In the view**

```js
{{ 
    datatable({ 
        'id' : 'dta-unique-id_1',
        ...
            'js' : {
            'sAjaxSource' : path('RouteForYour_grid_action_1')
            }
    }) 
}}

{{ 
    datatable({ 
        'id' : 'dta-unique-id_2',
        ...
        'js' : {
            'sAjaxSource' : path('RouteForYour_grid_action_2')
        }
    }) 
}}
```
