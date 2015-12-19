AliDatatableBundle
==================

[![Build Status](https://secure.travis-ci.org/AliHichem/AliDatatableBundle.png?branch=master)](http://travis-ci.org/AliHichem/AliDatatableBundle) 
<a href="https://codeclimate.com/github/AliHichem/AliDatatableBundle"><img src="https://codeclimate.com/github/AliHichem/AliDatatableBundle/badges/gpa.svg" /></a>
[![GitHub issues](https://img.shields.io/github/issues/AliHichem/AliDatatableBundle.svg)](https://github.com/AliHichem/AliDatatableBundle/issues)
[![GitHub forks](https://img.shields.io/github/forks/AliHichem/AliDatatableBundle.svg)](https://github.com/AliHichem/AliDatatableBundle/network)
[![GitHub stars](https://img.shields.io/github/stars/AliHichem/AliDatatableBundle.svg)](https://github.com/AliHichem/AliDatatableBundle/stargazers)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/AliHichem/AliDatatableBundle/master/LICENSE)


The Datatable bundle for symfony2 allow for easily integration of the [jQuery Datatable plugin](http://datatables.net/) with the doctrine2 entities having twitter bootstrap theme.
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
 * datatable configuration javascript code (even for multiple datatables) is grouped, minimized and moved to the bottom of the html body element automatically.
 * twitter bootstrap integration. 


<div style="text-align:center"><img alt="Screenshot" src="https://github.com/AliHichem/AliDatatableBundle/raw/master/Resources/public/images/sample_01.png"></div>

[Read the Documentation](https://github.com/AliHichem/AliDatatableBundle/blob/master/Resources/doc/index.md)