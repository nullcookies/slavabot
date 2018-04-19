
function getCSRF() {
    var metas = document.getElementsByTagName('meta');
    for (var i=0; i<metas.length; i++) {
        if (metas[i].getAttribute("name") ==="csrf-token") {
            return metas[i].getAttribute("content");
        }
    }
    return "";
}

function checkData($str, $len){
    return $str.length>$len;
}

function checkArray($arr, $param, $value){
    $status = false;
    for (var i = 0; i < $arr.length; i++) {
        if(parseInt($arr[i][$param]) === parseInt($value)){
            return $arr[i];
        }
    }
    return $status;
}

function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : false;
}

angular.module('cubeWebApp')
    .controller('dashboardCtrl', function ($scope, $http, $sce, $interval) {
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.vkLogin = '';
        $scope.vkPassword = '';
        $scope.vkError = false;
        $scope.vkLoginError = false;
        $scope.vkPasswordError = false;
        $scope.vkErrorText = '';
        $scope.vkAuthBox = true;
        $scope.vkGroupBox = false;
        $scope.vkWaitBox = false;
        $scope.vkfinish = false;
        $scope.userSelection = {};
        $scope.InstaLogin = '';
        $scope.InstaPassword = '';
        $scope.instaError = '';
        $scope.instaAuthBox = true;
        $scope.instafinish = false;
        $scope.sce = $sce;
        $scope.fbGroupBox = false;
        $scope.fbAuthBox = true;
        $scope.fbFinish = false;
        $scope.InstaWaitBox = false;

        $scope.InstaSave = function(){
            $scope.data = {
                Instagram:{
                    type: 'instagram',
                    data: {
                        'login': $scope.InstaLogin,
                        'password': $scope.InstaPassword,
                    }
                }
            };
            $scope.InstaWaitBox = true;
            $scope.instaAuthBox = false;

            if(checkData($scope.InstaLogin, 2) && checkData($scope.InstaPassword, 2)){
                $http.post('/social/instagram', $.param($scope.data), config).then(function success(response) {
                    if(response.data.error){
                        $scope.instaError = response.data.error;
                        $scope.InstaWaitBox = false;
                        $scope.instaAuthBox = true;
                        return false;
                    }else{
                        $scope.instaError = '';
                        $scope.InstaWaitBox = false;
                        $scope.instafinish = true;
                    }

                });
            }

        }

        $scope.checkUnprocessed = function(){
            $http.post('/social/unprocessed', [], config).then(function success(response) {
                if(response.data){
                    $scope.unprocessed = response.data.data.groups;
                    $scope.accountData = response.data.data;
                    $scope.unpID = response.data.id;
                    $scope.unprocessedType = response.data.type;
                    $scope.unprocessedName = response.data.data.user_name;
                    $scope.userSelection.activeValueVK = $scope.unprocessed[0].id;
                }
            });
        };

        $scope.checkUnprocessedFacebook = function(){
            $http.post('/social/unprocessed', { type : 'facebook'}, config).then(function success(response) {
                if(response.data.type=='facebook'){
                    $scope.unprocessed = response.data.data.groups;
                    $scope.accountData = response.data.data;
                    $scope.unpID = response.data.id;
                    $scope.unprocessedType = response.data.type;
                    $scope.unprocessedName = response.data.data.user_name;
                    $scope.fbGroupBox = true;
                    $scope.fbAuthBox = false;


                    $scope.userSelection.activeValueFB = $scope.unprocessed[0].id;
                }
            });
        };

        $scope.Timer = $interval(function () {
            if($scope.fbAuthBox){
                $scope.checkUnprocessedFacebook()
            }
        }, 1000);

        $scope.accountSave = function($type) {
            var data = $scope.accountData;

            if($type === 'vk'){
                data.groups = checkArray($scope.unprocessed, 'id',$scope.userSelection.activeValueVK);
            }else if($type === 'fb'){
                data.groups = checkArray($scope.unprocessed, 'id',$scope.userSelection.activeValueFB);
            }else{
                data.groups = $scope.userSelection.activeValue;
            }

            $scope.data = {
                id: $scope.unpID,
                data: data
            };

            $http.post('/social/finish-process', $.param($scope.data), config).then(function success(response) {
                if(response.data){
                    if($type=='vk'){
                        $scope.vkAuthBox = false;
                        $scope.vkGroupBox = false;
                        $scope.vkfinish = true;
                    }
                    if($type=='fb'){
                        $scope.fbGroupBox = false;
                        $scope.fbAuthBox = false;
                        $scope.fbFinish = true;
                    }
                }
            });
        };

        $scope.VkSave = function(){
            $scope.data = {
                login: $scope.vkLogin,
                password: $scope.vkPassword
            };

            $scope.vkWaitBox = true;
            $scope.vkAuthBox = false;

            if(checkData($scope.vkLogin, 9) && checkData($scope.vkPassword, 2)){
                $http.post('/social/vk-auth', $.param($scope.data), config).then(function success(response) {

                    if(response.data.status){
                        $scope.vkWaitBox = false;
                        $scope.vkError = false;
                        $scope.vkLoginError = false;
                        $scope.vkPasswordError = false;
                        $scope.vkErrorText = '';
                        $scope.checkUnprocessed();
                        $scope.vkGroupBox = true;

                    }else{
                        if(response.data.error==='login & pass are wrong'){
                            $scope.vkError = true;
                            $scope.vkAuthBox = true;
                            $scope.vkWaitBox = false;
                            $scope.vkLoginError = false;
                            $scope.vkPasswordError = false;
                            $scope.vkErrorText = 'Неверный логин/пароль';
                        }
                    }

                });
            }else{
                if(!checkData($scope.vkLogin, 9)){
                    $scope.vkLoginError = true;
                }

                if(!checkData($scope.vkPassword, 4)){
                    $scope.vkPasswordError = true;
                }

            }
        }

        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });
    })
    .controller('header', function ($scope, $http, $interval, $location) {

        $scope.telegramStatus = false;

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.getUserData = function(){
            $http.post('/system/main-data', {}, config).then(function success(response) {
                $scope.tariff = response.data.user.tariff;
                $scope.telegramStatus = response.data.user.telegram;
                $scope.UserName = response.data.user.name;

                // Единоразовый редирект на страницу тарифов, после окончания оплаченного периода

                // if(!$scope.tariff.active && !getCookie('payment_' +  $scope.tariff.payment_id)){
                //     setCookie('payment_' +  $scope.tariff.payment_id, true);
                //     $location.path('/tariffs');
                // }


            });
        };

        $scope.Timer = $interval(function () {
            $scope.getUserData()
        }, 10000);

        $scope.getUserData();
    })
    .controller('menu', function ($scope, $http, $interval) {

        $scope.potentialSubMenu = [];

        $scope.refreshMenu = function()
        {
            $http({method: 'GET', url: '/potential/filters'}).then(function success(response) {
                $scope.potentialSubMenu = response.data;
            });
        };

        $scope.refreshMenu();

        $scope.Timer = $interval(function () {
            $scope.refreshMenu();
        }, 1000);

        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });
    })
    .controller('potentialCtrl', function ($scope, $http, $sce, $location, $interval) {
        $scope.webhooks = [];
        $scope.city;
        $scope.country;
        $scope.region;
        $scope.sce = $sce;
        $scope.firstLoad = true;
        moment.locale('ru');
        $scope.search   = '';
        $scope.filterName = '';
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.nameError = false;
        $scope.noFilter = false;
        $scope.cityPlaceholder = 'Город';
        $scope.countryPlaceholder = 'Страна';
        $scope.regionPlaceholder = 'Регион';
        $scope.themePlaceholder = 'Тема';
        $scope.numberOfPages = 0;
        $scope.filterSuccess = false;
        $scope.filterError = false;
        $scope.owned = [];

        $scope.changeFilter = function($type){

            if($scope.country===null ){
                delete $scope.country;

                // $scope.region = null;
                // $scope.city = null;
            }

            if($scope.region===null || $type === 'country'){
                delete $scope.region;
                //$scope.city = null;
            }

            if($scope.city===null || $type === 'country' || $type === 'region'){
                delete $scope.city;
            }

            if($scope.theme==''){
                delete $scope.theme;
            }
            $scope.setPage($scope.currentPage);

        };

        $scope.time = moment(new Date());

        $scope.getContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/get-post', data, config).then(function success(response) {
                //$location.path('/potential/detail/'+id);
                $scope.setPage($scope.currentPage);
            });
        };

        $scope.dropContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/drop-post', data, config).then(function success(response) {
                //$location.path('/potential/detail/'+id);
                $scope.setPage($scope.currentPage);
            });
        };

        $scope.getList = function(){

            $http.get('/potential/list').then(function success(response) {
                $scope.user = response.data.user;
                $scope.owned = response.data.owned;
                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.locations = response.data.webhooks.location;
                $scope.countries = response.data.webhooks.countries;
                $scope.regions = response.data.webhooks.regions;
                $scope.themes = response.data.webhooks.theme;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                console.log(response);
            });
        };

        $scope.getList();

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
            console.log($scope.location);
            if($scope.filterName.length<3){
                $scope.nameError = true;
                return false;
            }

            if(
                $scope.search.length==0 &&
                $scope.city === undefined &&
                $scope.theme === undefined &&
                $scope.region === undefined &&
                $scope.country === undefined
            ){
                $scope.noFilter = true;
                return false;
            }

            $scope.nameError = false;
            $scope.noFilter = false;

            var data = $.param({
                name: $scope.filterName,
                search : $scope.search,
                location : $scope.city,
                theme : $scope.theme,
                region : $scope.region,
                country : $scope.country
            });

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/new-filter', data, config).then(function success(response) {
                if(response.data){
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }else{
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }
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
                'region' : $scope.region,
                'country' : $scope.country,
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
                $scope.firstLoad = false;
                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                $scope.currentPage = n;
            });
        };
        $scope.setPage($scope.currentPage);
        $scope.Timer = $interval(function () {
            $scope.setPage($scope.currentPage)
        }, 10000);

        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });

    })
    .controller('filterCtrl', function ($scope, $http, $sce, $routeParams,$location, $interval) {
        $scope.webhooks = [];
        $scope.locations = [];
        $scope.countries = [];
        $scope.regions = [];
        $scope.themes = [];

        $scope.city;
        $scope.country;
        $scope.region;
        $scope.theme;

        $scope.sce = $sce;

        moment.locale('ru');
        $scope.search   = '';
        $scope.filterName = '';
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.nameError = false;
        $scope.noFilter = false;
        $scope.cityPlaceholder = 'Город';
        $scope.countryPlaceholder = 'Страна';
        $scope.regionPlaceholder = 'Регион';
        $scope.themePlaceholder = 'Тема';
        $scope.numberOfPages = 0;
        $scope.notif = false;
        $scope.notifEmail = '';
        $scope.filterSuccess = false;
        $scope.filterError = false;

        $scope.changeFilter = function($type){

            if($scope.country===null ){
                delete $scope.country;
            }

            if($scope.region===null || $type === 'country'){
                delete $scope.region;
            }

            if($scope.city===null || $type === 'country' || $type === 'region'){
                delete $scope.city;
            }

            if($scope.theme==''){
                delete $scope.theme;
            }
            $scope.setPage($scope.currentPage);

        };

        $scope.shouldShowCity = function (location) {
            return ($scope.region===location.aRegion || !$scope.region) && ($scope.country===location.aCountry || !$scope.country);
        }

        $scope.shouldShowRegion = function (location) {
            return $scope.country===location.aCountry || !$scope.country;
        }

        $scope.time = moment(new Date());

        var id = $routeParams["id"];

        $http({method: 'GET', url: '/potential/filter?id='+id}).then(function success(response) {
            $scope.user = response.data.user;

            $scope.send_notification = response.data.filter.send_notification;
            $scope.owned = response.data.owned;

            $scope.locations = response.data.location;
            $scope.themes = response.data.theme;
            $scope.countries = response.data.countries;
            $scope.regions = response.data.regions;
            $scope.search = response.data.filter.search;

            $scope.city = response.data.filter.location;
            $scope.theme = response.data.filter.theme;
            $scope.filterName = response.data.filter.name;
            $scope.country = (response.data.filter.aCountry) ? response.data.filter.aCountry.toString() : response.data.filter.aCountry;
            $scope.region = (response.data.filter.aRegion) ? response.data.filter.aRegion.toString() : response.data.filter.aRegion;

            $scope.setPage(0);
        });

        $scope.getContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/get-post', data, config).then(function success(response) {
                //$location.path('/potential/detail/'+id);
                $scope.setPage($scope.currentPage);
            });
        };
        $scope.dropContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/drop-post', data, config).then(function success(response) {
                //$location.path('/potential/detail/'+id);
                $scope.setPage($scope.currentPage);
            });
        };

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
            if($scope.filterName.length<3){
                $scope.nameError = true;
                return false;
            }

            if(
                $scope.search.length==0 &&
                $scope.city === undefined &&
                $scope.theme === undefined &&
                $scope.region === undefined &&
                $scope.country === undefined
            ){
                $scope.noFilter = true;
                return false;
            }

            $scope.nameError = false;
            $scope.noFilter = false;


            var data = $.param({
                id: id,
                name: $scope.filterName,
                search: $scope.search,
                city: $scope.city,
                theme: $scope.theme,
                region : $scope.region,
                country : $scope.country,
                send_notification : $scope.send_notification,
            });

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/update-filter', data, config).then(function success(response) {
                if(response.data){
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }else{
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }
            });
        };
        $scope.dropFilter = function(){
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/drop-filter', $.param({id: id}), config).then(function success(response) {
                $location.path('/potential');
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
                'region' : $scope.region,
                'country' : $scope.country,
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

        //$scope.setPage($scope.currentPage);
        $scope.Timer = $interval(function () {
            console.log('Refresh request')

            $scope.setPage($scope.currentPage)
        }, 10000);

        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });
    })
    .controller('detailCtrl', function($scope, $http, $routeParams, $sce,$location){
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
                $scope.user = response.data.user;
                $scope.webhook = response.data.webhooks.webhooks;
            });
        };

        $scope.getDetail();
    })
    .controller('contactsCtrl', function ($scope, $http, $sce, $location, $interval) {
        $scope.webhooks = [];
        $scope.city;
        $scope.country;
        $scope.region;
        $scope.sce = $sce;
        $scope.firstLoad = true;
        moment.locale('ru');
        $scope.search   = '';
        $scope.filterName = '';
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.nameError = false;
        $scope.noFilter = false;
        $scope.cityPlaceholder = 'Город';
        $scope.countryPlaceholder = 'Страна';
        $scope.regionPlaceholder = 'Регион';
        $scope.themePlaceholder = 'Тема';
        $scope.numberOfPages = 0;
        $scope.filterSuccess = false;
        $scope.filterError = false;
        $scope.owned = [];

        $scope.changeFilter = function(){

            if($scope.country===''){

                delete $scope.country;

                $scope.region = '';
                $scope.city = '';
            }

            if($scope.region===''){
                delete $scope.region;
                $scope.city = '';
            }

            if($scope.city===''){
                delete $scope.city;
            }

            if($scope.theme==''){
                delete $scope.theme;
            }

            $scope.setPage(0);

        };

        $scope.time = moment(new Date());

        $scope.getContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/get-post', data, config).then(function success(response) {
                $location.path('/potential/detail/'+id);
            });
        };
        $scope.dropContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/drop-post', data, config).then(function success(response) {
                //$location.path('/potential/detail/'+id);
                $scope.setPage($scope.currentPage);
            });
        };

        $scope.getList = function(){

            $http.get('/potential/contacts').then(function success(response) {
                $scope.user = response.data.user;
                $scope.owned = response.data.owned;
                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.locations = response.data.webhooks.location;
                $scope.countries = response.data.webhooks.countries;
                $scope.regions = response.data.webhooks.regions;
                $scope.themes = response.data.webhooks.theme;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                console.log(response);
            });
        };

        $scope.getList();
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

            if($scope.filterName.length<3){
                $scope.nameError = true;
                return false;
            }

            if(
                $scope.search.length==0 &&
                $scope.location === undefined &&
                $scope.theme === undefined &&
                $scope.region === undefined &&
                $scope.country === undefined
            ){
                $scope.noFilter = true;
                return false;
            }

            $scope.nameError = false;
            $scope.noFilter = false;

            var data = $.param({
                name: $scope.filterName,
                search : $scope.search,
                location : $scope.location,
                theme : $scope.theme,
                region : $scope.region,
                country : $scope.country
            });

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/new-filter', data, config).then(function success(response) {
                if(response.data){
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }else{
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }
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
                'region' : $scope.region,
                'country' : $scope.country,
                'theme' : $scope.theme,
                'page' : n
            };

            var data = $.param($scope.pagination);

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/contacts', data, config).then(function success(response) {
                $scope.firstLoad = false;
                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                $scope.currentPage = n;
            });
        };
        $scope.setPage($scope.currentPage);
        $scope.Timer = $interval(function () {
            $scope.setPage($scope.currentPage)
        }, 10000);

        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });
    })
    .controller('helpCtrl', function ($scope, $http, $sce) {

        $scope.helpMassage = "";
        $scope.helpMassageError = false;
        $scope.success = false;
        $scope.serverError = false;

        function getCSRF() {
            var metas = document.getElementsByTagName('meta');
            for (var i=0; i<metas.length; i++) {
                if (metas[i].getAttribute("name") ==="csrf-token") {
                    return metas[i].getAttribute("content");
                }
            }
            return "";
        }

        $scope.sendMassage = function(){
            var text = $scope.helpMassage;
            if(text.length > 6){
                $scope.helpMassageError = false;
                $scope.data = {
                    'text' : text,
                };

                var data = $.param($scope.data);

                var config = {
                    headers : {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                        'X-CSRF-Token' : getCSRF()

                    }
                };

                $http.post('/system/help', data, config).then(function success(response) {
                    if(response){
                        $scope.success = true;
                        $scope.serverError = false;
                        $scope.helpMassageError = false;
                    }else{
                        $scope.serverError = true;
                    }
                });


            }else{
                $scope.helpMassageError = true;
            }
        };

    })
    .controller('configCtrl', function ($scope, $http, $sce) {

        $scope.mainPlane = true;
        $scope.passwordPlane = false;

        $scope.setMain = function(){
            $scope.mainPlane = true;
            $scope.passwordPlane = false;
        }
        $scope.setPassword = function(){
            $scope.mainPlane = false;
            $scope.passwordPlane = true;
        }

        $scope.username = '';
        $scope.timezone = '';
        $scope.phone = '';
        $scope.userSuccess = false;
        $scope.userError = false;
        $scope.password = '';
        $scope.new_password = '';
        $scope.new_password_repeat = '';

        function getCSRF() {
            var metas = document.getElementsByTagName('meta');
            for (var i=0; i<metas.length; i++) {
                if (metas[i].getAttribute("name") ==="csrf-token") {
                    return metas[i].getAttribute("content");
                }
            }
            return "";
        }

        function checkData($str, $len){
            return $str.length>$len;
        }

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $http.post('/site/user/', [], config).then(function success(response) {
            if (response) {
                $scope.timezone = response.data.timezone;
                $scope.username = response.data.username;
                $scope.phone = response.data.phone;
            }
        });

        $scope.saveUser = function() {
            $scope.data = {
                'UserConfig[username]': $scope.username,
                'UserConfig[phone]': $scope.phone,
                'UserConfig[timezone]': $scope.timezone,
            };

            var data = $.param($scope.data);

            if(checkData($scope.username, 2) && checkData($scope.phone, 10)){

                $http.post('/site/config/', data, config).then(function success(response) {
                    if (response) {
                        $scope.userSuccess = true;
                        $scope.userError = false;
                    } else {
                        $scope.userSuccess = false;
                        $scope.userError = true;
                    }
                });
            }

        };

        $scope.savePassword = function() {


            if(!checkData($scope.password, 2)){
                $scope.passwordError = true;


                return false;
            }

            if(!checkData($scope.new_password, 3)){
                $scope.new_passwordError = true;


                return false;
            }

            if($scope.new_password!=$scope.new_password_repeat){
                $scope.new_password_repeatError = true;


                return false;
            }


            $scope.passwordError = false;
            $scope.new_passwordError = false;
            $scope.new_password_repeatError = false;

            $scope.data = {
                'PasswordConfig[password]': $scope.password,
                'PasswordConfig[new_password]': $scope.new_password,
                'PasswordConfig[new_password_repeat]': $scope.new_password_repeat,
            };

            var data = $.param($scope.data);

            if(checkData($scope.password, 2) && checkData($scope.new_password, 3) && checkData($scope.new_password_repeat, 3) && $scope.new_password==$scope.new_password_repeat){

                $http.post('/site/config/', data, config).then(function success(response) {
                    if (response) {
                        $scope.userSuccess = true;
                        $scope.userError = false;
                    } else {
                        $scope.userSuccess = false;
                        $scope.userError = true;
                    }
                });
            }
        };
    })
    .controller('socialCtrl', function($scope, $http, $sce, $timeout){

        $scope.firstLoad = true;

        $scope.InstaLogin = '';
        $scope.InstaPassword = '';
        $scope.accounts = [];
        $scope.unprocessed = [];
        $scope.unpID = 0;
        $scope.userSelection = {};
        $scope.accountData = {};
        $scope.unprocessedName;
        $scope.activeID;
        $scope.removingID = 0;
        $scope.vkLogin;
        $scope.vkPassword;
        $scope.vkError = false;
        $scope.vkLoginError = false;
        $scope.vkPasswordError = false;
        $scope.vkErrorText = '';
        $scope.instaError = '';
        $scope.activeVKID;
        $scope.sce = $sce;

        $scope.InstaWaitBox = false;
        $scope.InstaFormBox = true;
        $scope.VkWaitBox = false;
        $scope.VkFormBox = true;

        $scope.available = {
            'instagram' : true,
            'facebook' : true,
            'vkontakte' : true
        };

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.checkUnprocessed = function(){
            $http.post('/social/unprocessed', [], config).then(function success(response) {
                if(response.data){
                    $scope.unprocessed = response.data.data.groups;
                    $scope.accountData = response.data.data;
                    $scope.unpID = response.data.id;
                    $scope.unprocessedType = response.data.type;
                    $scope.unprocessedName = response.data.data.user_name;
                    document.getElementById('getUnprocessed').click();
                }
            });
        };

        $scope.checkUnprocessed();

        $scope.getAccounts = function() {
            $http.post('/social/accounts', [], config).then(function success(response) {
                $scope.accounts = response.data;
                $scope.available = {
                    'instagram' : true,
                    'facebook' : true,
                    'vkontakte' : true
                };
                for (key in $scope.accounts) {
                    $scope.available[$scope.accounts[key].type] = false;
                }
            });
            $scope.firstLoad = false;
        }

        $scope.remove = function() {
            if($scope.removingID>0){
                $id = $scope.removingID;
                $http.post('/social/remove', $.param({id: $id}), config).then(function success(response) {
                    if(response.data){
                        $scope.getAccounts();
                        $scope.clearInstaForm();
                        document.getElementById('closeConfirmModal').click();
                    }
                });
            }

        };

        $scope.showConfirm = function($id){
            $scope.removingID = $id;

            document.getElementById('confirmModal').click();
        };

        $scope.refresh = function($id, $type) {
            $http.post('/social/update-process', $.param({id: $id}), config).then(function success(response) {
                document.getElementById($type).click();
            });
        };

        $scope.instagramRefresh = function($account) {

            $scope.activeID = $account.id;
            $scope.InstaLogin = $account.data.login;
            $scope.InstaPassword = $account.data.password;

            document.getElementById('instagram').click();

        };

        $scope.VKRefresh = function($account) {

            $scope.activeVKID = $account.id;
            $scope.vkLogin = $account.data.login;
            $scope.vkPassword = $account.data.password;

            document.getElementById('vkontakte').click();
        };

        $scope.clearInstaForm = function(){
            $scope.InstaLogin = '';
            $scope.InstaPassword = '';
            $scope.activeID = '';
            $scope.removingID = 0;
        };

        $scope.clearVkForm = function(){
            $scope.vkLogin = '';
            $scope.vkPassword = '';
        };
        $scope.getAccounts();

        $scope.VkSave = function(){

            if($scope.activeVKID>0){
                $scope.data = {
                    id: $scope.activeVKID,
                    login: $scope.vkLogin,
                    password: $scope.vkPassword
                };
            }else{
                $scope.data = {
                    login: $scope.vkLogin,
                    password: $scope.vkPassword
                };
            }

            if(checkData($scope.vkLogin, 9) && checkData($scope.vkPassword, 2)){
                $scope.VkWaitBox = true;
                $scope.VkFormBox = false;
                $http.post('/social/vk-auth', $.param($scope.data), config).then(function success(response) {
                    $scope.VkWaitBox = false;
                    $scope.VkFormBox = true;

                    if(response.data.status){
                        $scope.vkError = false;
                        $scope.vkLoginError = false;
                        $scope.vkPasswordError = false;
                        $scope.vkErrorText = '';
                        document.getElementById('closemyModalVK').click();
                        $scope.clearVkForm();
                        $scope.getAccounts();
                        $scope.checkUnprocessed();
                    }else{
                        if(response.data.error==='login & pass are wrong'){
                            $scope.vkError = true;
                            $scope.vkLoginError = false;
                            $scope.vkPasswordError = false;
                            $scope.vkErrorText = 'Неверный логин/пароль';
                        }
                    }

                });
            }else{
                if(!checkData($scope.vkLogin, 9)){
                    $scope.vkLoginError = true;
                }

                if(!checkData($scope.vkPassword, 4)){
                    $scope.vkPasswordError = true;
                }

            }

        }

        $scope.accountSave = function() {
            var data = $scope.accountData;

            data.groups = $scope.userSelection.activeValue;
            $scope.data = {
                id: $scope.unpID,
                data: data
            };

            $http.post('/social/finish-process', $.param($scope.data), config).then(function success(response) {
                if(response.data){
                    document.getElementById('closeModal').click();
                    $scope.getAccounts();
                }
            });
        };

        $scope.InstaSave = function(){

            if($scope.activeID>0){
                $scope.data = {
                    Instagram :{
                        id: $scope.activeID,
                        type: 'instagram',
                        data: {
                            'login': $scope.InstaLogin,
                            'password': $scope.InstaPassword,
                        }
                    }
                };
            }else{
                $scope.data = {
                    Instagram :
                        {
                            type: 'instagram',
                            data: {
                                'login': $scope.InstaLogin,
                                'password': $scope.InstaPassword,
                            }
                        }
                };
            }


            if(checkData($scope.InstaLogin, 2) && checkData($scope.InstaPassword, 2)){

                $scope.InstaWaitBox = true;
                $scope.InstaFormBox = false;

                $http.post('/social/instagram', $.param($scope.data), config).then(function success(response) {

                    $scope.InstaWaitBox = false;
                    $scope.InstaFormBox = true;

                    if(response.data.error){
                        $scope.instaError = response.data.error;
                        $timeout($scope.ClearError, 5000);
                        return false;
                    }else{
                        $scope.instaError = '';
                    }

                    document.getElementById('closeInstaModal').click();
                    $scope.clearInstaForm();
                    $scope.getAccounts();

                });
            }

        }

        $scope.ClearError = function(){
            $scope.instaError = '';
        };
    })
    .controller('historyCtrl', function($scope, $http, $sce, $interval){
        $scope.firstLoad = true;
        $scope.history = []; // Массив элементов истории
        $scope.order = 'desc'; // Сортировка по умолчанию
        $scope.planned = [];

        // Пагинация
        $scope.numberOfPages = 0; // Количество страниц
        $scope.currentPage = 0;   // Текущая страница
        $scope.pageSize = 10;     // Количество элементов на странице

        $scope.plannedCurrentPage = 0;   // Текущая страница

        //Вкладки
        $scope.allPlane = true;
        $scope.plannedPlane = false;

        $scope.setAll = function(){
            $scope.allPlane = true;
            $scope.plannedPlane = false;
        };
        $scope.setPlanned = function(){
            $scope.allPlane = false;
            $scope.plannedPlane = true;
        };

        moment.locale('ru'); // Локализация для отображения даты

        // Цепляем CSRF-Token для отправки post-запроса

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        // Функция для обработки всех вариантов JSON, приходящих от бота.

        $scope.parJson = function (json) {
            var res;
            try{
                res = JSON.parse(JSON.parse(json));
            }catch(e){
                try{
                    res = JSON.parse(json);
                }catch(e){
                    res = json;
                }
            }
            return res;
        }
        $scope.fixMonth = function($mon){
            return $mon + 1;
        }
        $scope.getTime = function($time) {

            var date = new Date( $time * 1000 );

            var now = new Date();
            now = now.getDate() + '.' + $scope.fixMonth(now.getMonth()) + '.' + now.getFullYear();
            var day = date.getDate() + '.' + $scope.fixMonth(date.getMonth()) + '.' + date.getFullYear();
            var hours = date.getHours();

            hours = hours <10?'0'+hours:''+hours;

            var minutes = date.getMinutes();

            minutes = minutes <10?'0'+minutes:''+minutes;

            var seconds = date.getSeconds();

            seconds = seconds <10?'0'+seconds:''+seconds;

            if(now!=day){
                var time = day + ' ' + hours + ':' + minutes;
            }else{
                var time = hours + ':' + minutes;
            }


            return time;
        }

        // Получить элементы истории

        $scope.getList = function($data){
            $data = $data || [];
            $http.post('/history/get-list', $data, config).then(function success(response) {
                $scope.history = response.data.history;
                $scope.pages = response.data.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
                $scope.firstLoad = false;
            });
        };

        // Получить запланированные
        $scope.getPlanned = function($data){
            $data = $data || [];
            $http.post('/history/get-planned', $data, config).then(function success(response) {
                $scope.planned = response.data.history;
                $scope.plannedPages = response.data.pages;
                $scope.plannedNumberOfPages = $scope.plannedPages.totalCount / $scope.pageSize;
                $scope.firstLoad = false;
            });
        };

        // Получаем при загрузке элементы истории

        //$scope.getList();

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
        $scope.changeOrder = function(){

            if($scope.order == 'desc'){
                $scope.order = 'asc';
            }else{
                $scope.order = 'desc';
            }
            $scope.setPlannedPage($scope.currentPage);
            $scope.setPage($scope.currentPage);
        };
        $scope.disabledBack = function() {
            if($scope.currentPage == 0){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage-1
                $scope.setPage($scope.currentPage);
            }
        };

        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
                $scope.setPage($scope.currentPage);
            }
        };

        $scope.disabledPlaneBack = function() {
            if($scope.plannedCurrentPage == 0){
                return false;
            }else{
                $scope.plannedCurrentPage = $scope.plannedCurrentPage-1
                $scope.setPlannedPage($scope.plannedCurrentPage);
            }
        };

        $scope.disabledPlaneNext = function() {
            if($scope.plannedCurrentPage >= $scope.plannedNumberOfPages - 1){
                return false;
            }else{
                $scope.plannedCurrentPage = $scope.plannedCurrentPage+1
                $scope.setPlannedPage($scope.plannedCurrentPage);
            }
        };

        $scope.setPage = function(n){

            $scope.getList($.param({'page' : n, 'order' : $scope.order}));
            $scope.currentPage = n;
        };
        $scope.setPlannedPage = function(n){

            $scope.getPlanned($.param({'page' : n, 'order' : $scope.order}));
            $scope.plannedCurrentPage = n;
        };

        $scope.setPlannedPage($scope.currentPage);
        $scope.setPage($scope.currentPage);

        // $scope.Timer = $interval(function () {
        //     $scope.setPlannedPage($scope.currentPage);
        //     $scope.setPage($scope.currentPage);
        // }, 5000);

    })
    .controller('notificationCtrl', function($scope, $http, $sce, $interval){

        $scope.userNotifications = [];
        $scope.notificationMessage = [];
        $scope.LoadInProgress = true;
        moment.locale('ru');

        // Пагинация

        $scope.numberOfPages = 0; // Количество страниц
        $scope.currentPage = 0;   // Текущая страница
        $scope.pageSize = 10;     // Количество элементов на странице

        $scope.disabledBack = function() {
            if($scope.currentPage == 0){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage-1
                $scope.setPage($scope.currentPage);
            }
        };

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

        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
                $scope.setPage($scope.currentPage);
            }
        };

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.getNotifications = function($data){

            $data = $data || [];

            $http.post('/notification/notifications', $data, config).then(function success(response) {
                $scope.userNotifications = response.data.notifications;
                $scope.pages = response.data.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
                $scope.LoadInProgress = false;
            });
        };


        $scope.setPage = function(n){
            $scope.getNotifications($.param({'page' : n}));
            $scope.currentPage = n;
        };
        $scope.Timer = $interval(function () {
            $scope.setPage($scope.currentPage);
        }, 10000);

        $scope.setPage(0);
        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });
    })
    .controller('userNotificationCtrl', function($scope, $http, $sce, $interval, $routeParams, $location){

        $scope.chatPane = true;
        $scope.postsPane = false;

        $scope.setChat = function(){
            $scope.chatPane = true;
            $scope.postsPane = false;
        }

        $scope.setPosts = function(){
            $scope.chatPane = false;
            $scope.postsPane = true;
        }

        $scope.message = '';
        $scope.comment = [];
        $scope.posts = [];
        $scope.social = '';
        $scope.mediaID = 0;

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };
        $scope.parJson = function (json) {
            var res;
            try{
                res = JSON.parse(JSON.parse(json));
            }catch(e){
                try{
                    res = JSON.parse(json);
                }catch(e){
                    res = json;
                }
            }
            return res;
        }
        $scope.getNotificationsForUser = function(){
            moment.locale('ru');


            var data = $.param({'id' : $routeParams["id"]});


            $http.post('/notification/user-notifications', data, config).then(function success(response) {
                if(response.data){
                    if(!response.data.access){
                        $location.path('/pages/notice');
                    }else{
                        $scope.data = response.data.peer;
                        $scope.userNotification = $scope.data.notification;
                        $scope.userAvatar = $scope.data.avatar;
                        $scope.userName = $scope.data.title;
                        $scope.user_id = response.data.user;
                        $scope.peer_id = $scope.data.peer_id;
                        $scope.mediaID = $scope.data.media_id.info;
                        $scope.social = $scope.data.social;
                        $scope.posts = response.data.posts;

                        if($scope.userNotification.length===0){
                            $scope.setPosts();
                        }

                        document.getElementById('refresh').click();
                    }
                }
            });
        };

        $scope.sendMessage = function(){
            if($scope.social=='VK'){
                $data = $.param({'user_id' : $scope.user_id, 'peer_id' : $scope.peer_id, 'message': $scope.message});
                $scope.message = '';
                $http.post('/rest/send/v1/vk-message', $data, config).then(function success(response) {


                });
            }else if($scope.social=='FB'){
                console.log($scope.data.psid);
                $data = $.param({'user_id' : $scope.user_id, 'peer_id' : $scope.data.psid, 'message': $scope.message});
                $scope.message = '';
                $http.post('/rest/send/v1/fb-message', $data, config).then(function success(response) {


                });
            }
        };

        $scope.sendComment = function(post_id){
            if($scope.social=='IG') {
                $data = $.param({
                    'user_id': $scope.user_id,
                    'peer_id': $scope.peer_id,
                    'media_id': post_id,
                    'message': $scope.comment[post_id]
                });

                $scope.comment[post_id] = '';

                $http.post('/rest/send/v1/ig-comment', $data, config).then(function success(response) {
                    $scope.getNotificationsForUser();
                });
            }

            if($scope.social=='FB') {
                $data = $.param({
                    'user_id': $scope.user_id,
                    'peer_id': $scope.peer_id,
                    'media_id': post_id,
                    'message': $scope.comment[post_id]
                });

                $scope.comment[post_id] = '';

                $http.post('/rest/send/v1/fb-comment', $data, config).then(function success(response) {
                    $scope.getNotificationsForUser();
                });
            }

            if($scope.social=='VK') {
                $data = $.param({
                    'user_id': $scope.user_id,
                    'peer_id': post_id.split('_')[0],
                    'media_id': post_id.split('_')[1],
                    'message': $scope.comment[post_id]
                });

                $scope.comment[post_id] = '';

                $http.post('/rest/send/v1/vk-comment', $data, config).then(function success(response) {
                    $scope.getNotificationsForUser();
                });
            }
        };

        $scope.getNotificationsForUser();

        $scope.Timer = $interval(function () {
            $scope.getNotificationsForUser();
        }, 10000);

        $scope.$on("$destroy", function (event) {
            $interval.cancel($scope.Timer);
        });

    })
    .controller('tariffsCtrl', function($scope, $http, $sce, $interval){
        $scope.tariffs = [];

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.getTariffs = function(){
            $http.post('/billing/tariffs', [], config).then(function success(response) {
                $scope.tariffs = response.data;
            });
        };

        $scope.getTariffs();

    })
    .controller('paymentCtrl', function($scope, $http, $sce, $routeParams){
        $scope.tariff = [];
        $scope.pay = false;
        $scope.payMarkUp = '';
        $scope.count = 3;
        $scope.sce = $sce;


        var data = $.param({'id' : $routeParams["id"]});

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.getTariffs = function(){
            $http.post('/billing/tariffs/get', data, config).then(function success(response) {
                $scope.tariff = response.data;

                console.log($scope.tariff);
            });
        };

        $scope.submit = function(){
            $http.post(
                '/billing/order',
                $.param(
                    {
                        'id' : $scope.tariff.id,
                        'count':$scope.count}
                        ),
                config
            ).then(function success(response) {
                $http.post(
                    '/payment',
                    $.param(
                        {
                            'order' : response.data.order_id
                        }
                    ),
                    config
                ).then(function success(response) {
                    $scope.pay = true;
                    $scope.payMarkUp = response.data;
                });
            });
        };

        $scope.getTariffs();
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

            if(total>30 ){
                total = 30;
            }
            for (var i=0; i<total; i++) {
                input.push(i);
            }
            return input;
        };
    });

    app.filter("trustUrl", ['$sce', function ($sce) {
        return function (recordingUrl) {
            return '/storage/download/'+$sce.trustAsResourceUrl(recordingUrl);
        };
    }]);




