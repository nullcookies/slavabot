<?
use frontend\controllers\SocialController;
?>

<div class="col-lg-12 ng-scope">
    <div class="main-box clearfix">
        <div class="main-box-body clearfix">
            <div class="row">
                <div class="col-md-12">
                    <div style="margin-top:25px;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Привязать соц.сеть <span class="caret"></span></button>
                                <ul class="dropdown-menu accountsMenu" role="menu">
                                    <li><a href="#">Facebook</a></li>
                                    <li>
                                        <?=
                                            SocialController::getVKBtn(
                                                'http://'.$_SERVER['SERVER_NAME'].'/social/vk',
                                                'Вконтакте',
                                                'vkontakte',
                                                \Yii::$app->user->identity->id
                                            );
                                        ?>
                                    </li>
                                    <li><a data-toggle="modal" id="instagram" data-target="#myModal">Instagram</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:25px;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Список привязанных аккаунтов:</h2>
                            <br>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><span>Соц.сеть</span></th>
                                        <th><span>Имя аккаунта</span></th>
                                        <th class="text-center"><span>Статус</span></th>
                                        <th>
                                            Доступные аккаунты групп
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="account in accounts" ng-show="account.processed===1">
                                        <td>
                                            {{account.type}}
                                        </td>
                                        <td ng-show="account.type === 'instagram'">
                                            {{account.data.login}}
                                        </td>
                                        <td ng-show="account.type === 'vkontakte'">
                                            {{account.data.user_name}}
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-success" ng-show="account.status === 1">Активна</span>
                                            <span class="label label-danger" ng-show="account.status === 0">Ошибка</span>
                                            <span class="label label-warning" ng-show="account.status === null">Проверка</span>
                                        </td>
                                        <td ng-show="account.type === 'instagram'">
                                            <table class="noTop">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        {{account.data.login}}
                                                    </td>
                                                    <td style="width: 15%;">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-primary" ng-click="instagramRefresh(account)">Обновить</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td ng-show="account.type === 'vkontakte'">
                                            <table class="noTop">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        {{account.data.groups.name}}
                                                    </td>
                                                    <td style="width: 15%;">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-primary" ng-click="refresh(account.id, account.type)">Обновить</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger" ng-click="remove(account.id)">Удалить</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" id="closeInstaModal" class="close" data-dismiss="modal" ng-click="clearInstaForm()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Добавить аккаунт Instagram</h4>
                    </div>
                    <div class="modal-body">
                        <form role="form" class="ng-pristine ng-valid">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Логин</label>
                                <input type="text" class="form-control" ng-model="InstaLogin" placeholder="Номер телефона, имя пользователя или эл. адрес">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPassword1">Пароль</label>
                                <input type="password" class="form-control" ng-model="InstaPassword" placeholder="Пароль">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" ng-click="InstaSave()">Сохранить</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"  ng-click="clearInstaForm()">Отмена</button>
                    </div>
                </div>
            </div>
        </div>

        <a data-toggle="modal" id="getUnprocessed" style="display:none" data-target="#myModalGrope"></a>

        <div class="modal fade" id="myModalGrope" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" id="closeModal" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Укажите активное сообщество для аккаунта: <br> [{{unprocessedType}}] {{unprocessedName}}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="radio" style="margin-top: 25px;" ng-repeat="groupe in unprocessed">
                                <input type="radio" name="optionsRadios" ng-model="userSelection.activeValue" id="optionsRadios{{groupe.id}}" ng-value="groupe">
                                <label for="optionsRadios{{groupe.id}}">
                                    <img src="{{groupe.photo_50}}" alt="" style="margin-top: -15px; border-radius: 100%;">
                                    <span style="margin-left: 10px; display: block; float: right;">{{groupe.name}}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" ng-click="accountSave()">Сохранить</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
