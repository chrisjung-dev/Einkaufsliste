(function () {
    "use strict";

    var Einkaufsliste = angular.module( "Einkaufsliste", ['ngRoute', 'ngAnimate'] );

    var path_db = "db/index.php";

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
                    } )
                    .otherwise( { redirectTo: '/'} );
            }
        ] )
        .controller( 'EinkaufslisteController', function () {
        } )
        .controller( 'ListController', function ( $http, $location ) {

            var list = this;
            this.newEntry = {};
            this.Entries = [];

            this.loadList = function () {
                $http.get( path_db + "/list" )
                    .success( function ( data ) {
                        list.isOffline = false;
                        list.Entries = data;
                        try {
                            window.localStorage.clear();
                            window.localStorage.setItem( 'items', JSON.stringify( data ) );
                        } catch ( e ) {
                            // nothing to do
                        }
                    } )
                    .error( function ( error ) {
                        list.isOffline = true;
                        try {
                            list.Entries = JSON.parse( window.localStorage.getItem( 'items' ) );
                        } catch ( e ) {
                            // nothing to do
                        }
                    } )
            };

            this.addListEntry = function () {

                if ( this.newEntry.name && this.newEntry.amount ) {

                    var newEntry = {
                        name: this.newEntry.name,
                        amount: this.newEntry.amount.toString()
                    };


                    $location.path( '/' );

                    $http
                        .post( path_db + '/add', newEntry )
                        .success( function ( data ) {
                            list.isOffline = false;
                            if ( data !== [] ) {
                                list.Entries.push( data );
                                list.newEntry = {};
                            }
                        } )
                        .error( function () {
                            $location.path( '/new' );
                            list.isOffline = true;
                        } );
                }

            };

            this.deleteEntries = function () {

                var _delete = {'delete': [] };

                for ( var idx in list.Entries ) {

                    var entry = list.Entries[idx];
                    if ( entry.delete ) {
                        _delete.delete.push( entry.id );
                    }
                }

                // only post if there are entries to delete
                if ( _delete.delete.length ) {
                    $http
                        .post( path_db + "/delete", _delete )
                        .success( function ( data ) {
                            list.isOffline = false;

                            if ( data.status === "ok" ) {
                                list.loadList();
                            }
                        } )
                        .error(function(){
                            list.isOffline = true;
                        });
                }

            };
            /**
             *  Toggle checkbox from wrapping LI, too (usability)
             * @param _entry
             */
            this.toggleEntryDelete = function ( _entry ) {
                _entry.delete = _entry.delete ? false : true;
            };

            this.loadList();

        } )
        .controller( 'NavigationController', function ( $location ) {

            this.isActive = function ( viewLocation ) {
                return viewLocation === $location.path();
            };

        } )
})();