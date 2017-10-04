angular.module('cubeWebApp')

    .controller('dashboardCtrl', function ($scope, $http) {

        $scope.category = [];
        $scope.location = [];
        $scope.priority = [];
        $scope.theme = [];
        $scope.webhooks = 0;

        $http({method: 'GET', url: '/site/main'}).
        then(function success(response) {
            console.log(response);

            $scope.category = response.data.category;
            $scope.location = response.data.location;
            $scope.priority = response.data.priority;
            $scope.theme = response.data.theme;
            $scope.webhooks = response.data.webhooks;
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

        $scope.numberOfPages = function(){
            return Math.ceil($scope.webhooks.length / $scope.pageSize);
        }

        $scope.time = moment(new Date());

        $http({method: 'GET', url: '/potential/list'}).then(function success(response) {
            $scope.webhooks = response.data.webhooks.webhooks;
            $scope.locations = response.data.webhooks.location;
            $scope.themes = response.data.webhooks.theme;
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

            console.log(JSON.stringify($scope.arrFilter));

        };

        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages() - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
            }
        };

        $scope.setPage = function(n){
            $scope.currentPage = n;
        };
    });

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




