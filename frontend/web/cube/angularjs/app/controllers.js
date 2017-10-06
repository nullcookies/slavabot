angular.module('cubeWebApp')
    .controller('dashboardCtrl', function ($scope, $http) {

        $scope.category = [];
        $scope.location = [];
        $scope.priority = [];
        $scope.theme = [];
        $scope.webhooks = 0;

        $http({method: 'GET', url: '/site/main'}).
        then(function success(response) {
            $scope.indb = response.data.indb;
            $scope.vk = response.data.vk;
            $scope.ok = response.data.ok;
            $scope.fb = response.data.fb;
            $scope.twitter = response.data.twitter;
            $scope.inst = response.data.inst;
            $scope.norm = response.data.norm;

            $scope.webhooks = response.data.webhooks;
        });
    })
    .controller('header', function ($scope, $http) {})
    .controller('menu', function ($scope, $http) {

        $scope.potentialSubMenu = [];

        $http({method: 'GET', url: '/potential/filters'}).then(function success(response) {
            $scope.potentialSubMenu = response.data;
        });
    })
    .controller('potentialCtrl', function ($scope, $http, $sce) {
        $scope.webhooks = [];
        $scope.city;

        $scope.sce = $sce;
        moment.locale('ru');
        $scope.search   = '';
        $scope.filterName = '';
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.nameError = false;
        $scope.noFilter = false;
        $scope.cityPlaceholder = 'Город';
        $scope.themePlaceholder = 'Тема';
        $scope.numberOfPages = 0;

        $scope.changeFilter = function(){

            if($scope.city==''){
                delete $scope.city;
            }

            if($scope.theme==''){
                delete $scope.theme;
            }

            $scope.setPage(0);

        };
        $scope.time = moment(new Date());

        $http({method: 'GET', url: '/potential/list'}).then(function success(response) {
            $scope.webhooks = response.data.webhooks.webhooks;
            $scope.locations = response.data.webhooks.location;
            $scope.themes = response.data.webhooks.theme;
            $scope.pages = response.data.webhooks.pages;
            $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
        });

        $scope.paginationBlock = function(n){
            if($scope.currentPage < 4 && n < 7){
                return true;
            }else{
                if($scope.currentPage >  n + 3 || $scope.currentPage <  n - 3){
                    return false;
                }else{
                    return true;
                }
            }

        };
        $scope.disabledBack = function() {
            if($scope.currentPage == 0){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage-1
            }
        };
        $scope.saveFilter = function() {
            $scope.arrFilter = {
                'search' : $scope.search,
                'city' : $scope.city,
                'theme' : $scope.theme
            };

            if($scope.filterName.length<3){
                $scope.nameError = true;
                return false;
            }

            if($scope.search.length==0 && $scope.city === undefined && $scope.theme === undefined){
                $scope.noFilter = true;
                return false;
            }

            $scope.nameError = false;
            $scope.noFilter = false;

            var data = $.param({
                name: $scope.filterName,
                filter: JSON.stringify($scope.arrFilter)
            });

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            }

            $http.post('/potential/new-filter', data, config).then(function success(response) {
                console.log(response);
            });
        };
        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages() - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
            }
        };
        $scope.setPage = function(n){

            $scope.pagination = {
                'search' : $scope.search,
                'city' : $scope.city,
                'theme' : $scope.theme,
                'page' : n
            };

            var data = $.param($scope.pagination);

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/list', data, config).then(function success(response) {

                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                $scope.currentPage = n;
            });
        };
    })
    .controller('filterCtrl', function ($scope, $http, $sce, $routeParams,$location) {
    $scope.webhooks = [];
    $scope.locations = [];
    $scope.themes = [];
    $scope.city;

    $scope.sce = $sce;
    moment.locale('ru');
    $scope.search   = '';
    $scope.filterName = '';
    $scope.currentPage = 0;
    $scope.pageSize = 10;
    $scope.nameError = false;
    $scope.noFilter = false;
    $scope.cityPlaceholder = 'Город';
    $scope.themePlaceholder = 'Тема';
    $scope.numberOfPages = 0;

    //     #/pages/contacts



    $scope.changeFilter = function(){

        if($scope.city==''){
            delete $scope.city;
        }
        if($scope.theme==''){
                delete $scope.theme;
        }

        $scope.setPage(0);

    };
    $scope.time = moment(new Date());

    var id = $routeParams["id"];

    $http({method: 'GET', url: '/potential/filter?id='+id}).then(function success(response) {
        $scope.filter = response.data.filter;
        $scope.filter.filter = JSON.parse(response.data.filter.filter);

        $scope.filterName = $scope.filter.name;

        $scope.locations = response.data.location;
        $scope.themes = response.data.theme;

        $scope.search = $scope.filter.filter.search;
        $scope.city = $scope.filter.filter.city;
        $scope.theme = $scope.filter.filter.theme;

        $scope.setPage(0);
    });

    $scope.paginationBlock = function(n){
        if($scope.currentPage < 4 && n < 7){
            return true;
        }else{
            if($scope.currentPage >  n + 3 || $scope.currentPage <  n - 3){
                return false;
            }else{
                return true;
            }
        }

    };
    $scope.disabledBack = function() {
        if($scope.currentPage == 0){
            return false;
        }else{
            $scope.currentPage = $scope.currentPage-1
        }
    };
    $scope.saveFilter = function() {
        $scope.arrFilter = {
            'search' : $scope.search,
            'city' : $scope.city,
            'theme' : $scope.theme
        };

        if($scope.filterName.length<3){
            $scope.nameError = true;
            return false;
        }

        if($scope.search.length==0 && $scope.city === undefined && $scope.theme === undefined){
            $scope.noFilter = true;
            return false;
        }

        $scope.nameError = false;
        $scope.noFilter = false;

        var data = $.param({
            id: id,
            name: $scope.filterName,
            filter: JSON.stringify($scope.arrFilter)
        });

        var config = {
            headers : {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            }
        }

        $http.post('/potential/update-filter', data, config).then(function success(response) {
            console.log(response);
        });
    };
    $scope.disabledNext = function() {
        if($scope.currentPage >= $scope.numberOfPages() - 1){
            return false;
        }else{
            $scope.currentPage = $scope.currentPage+1
        }
    };
    $scope.setPage = function(n){

        $scope.pagination = {
            'search' : $scope.search,
            'city' : $scope.city,
            'theme' : $scope.theme,
            'page' : n
        };

        var data = $.param($scope.pagination);
        var config = {
            headers : {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            }
        };

        $http.post('/potential/list', data, config).then(function success(response) {

            $scope.webhooks = response.data.webhooks.webhooks;
            $scope.pages = response.data.webhooks.pages;
            $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
            $scope.currentPage = n;
        });
    };
})
    .controller('detailCtrl', function($scope, $http, $routeParams, $sce){
        $scope.webhook = [];
        $scope.sce = $sce;
        moment.locale('ru');
        $scope.getDetail = function(n){

            var data = $.param({'id' : $routeParams["id"]});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/detail', data, config).then(function success(response) {
                $scope.webhook = response.data.webhooks.webhooks;
                console.log($scope.webhook);
            });
        };

        $scope.getDetail();
    })
app.filter('startFrom', function() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
});

app.filter('range', function() {
    return function(input, total) {
        total = Math.ceil(total);
        for (var i=0; i<total; i++) {
            input.push(i);
        }
        return input;
    };
});




