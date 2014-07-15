(function () {
    "use strict";

    var Einkaufsliste = angular.module( "Einkaufsliste", ['ngRoute', 'ngAnimate'] );

    var path_db = "db/";

    Einkaufsliste
        .config( [
            '$routeProvider',
            function ( $routeProvider ) {

                $routeProvider
                    .when( '/', {
                        templateUrl: 'src/views/list.tpl.html'
                    } )
                    .when( '/new', {
                        templateUrl: 'src/views/new.tpl.html'
                    } )
                    .when( '/about', {
                        templateUrl: 'about.html',
                        controller: 'NavigationController',
                        controllerAs: 'nav'
                    })
                    .otherwise( { redirectTo: '/'} );
            }
        ] )
        .controller( 'ListController', function ( $http ) {

            var list = this;
            this.newEntry = {};
            this.Entries = [];

            this.loadList = function () {
                $http.get( path_db + "?action=read" ).success( function ( data ) {
                    list.Entries = data;
                } )
            };

            this.addListEntry = function () {

                if ( this.newEntry.name && this.newEntry.amount )

                    var shaObj = new jsSHA( this.newEntry.name + this.newEntry.amount, "TEXT" ),
                        hash = shaObj.getHash( "SHA-1", "HEX" );

                var newEntry = {
                    hash: hash,
                    name: this.newEntry.name,
                    amount: this.newEntry.amount.toString()
                };

                location.hash = "/";

                $http
                    .post( path_db + '?action=add', newEntry )
                    .success( function ( data ) {
                        list.Entries.push( newEntry );
                    } );

                this.newEntry = {};

            };

            this.deleteEntries = function () {

                var _delete = {'delete': [] };

                for ( var idx in list.Entries ) {

                    var entry = list.Entries[idx];

                    if ( entry.delete ) {
                        _delete.delete.push( entry.hash );
                    }
                }

                $http.post( path_db + "?action=delete", _delete )
                    .success( function ( data ) {
                        if ( data.status === "ok" ) {
                            list.loadList();
                        }
                    } );

            };

            this.loadList();

        } )
        .controller( 'NavigationController', function(){
            this.active = 1;

            this.setActive = function(newValue){
                this.active = newValue;
            };

            this.isActive = function(nav){
                return this.active === nav;
            };

        })


})();