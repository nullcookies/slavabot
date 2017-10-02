<?php

/* @var $this yii\web\View */

$this->title = 'Потенциальные клиенты';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <h2 class="pull-left">Количество: <?=$count?></h2>

                <div id="reportrange" class="pull-right daterange-filter">
                    <i class="icon-calendar"></i>
                    <span></span> <b class="caret"></b>
                </div>
            </header>

            <div class="main-box-body clearfix">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th><span>MLG ID</span></th>
                            <th><span>Тема</span></th>
                            <th><span>Имя автора</span></th>
                            <th><span>Содержимое поста</span></th>
                            <th><span>Регион</span></th>
                            <th><span>Категория</span></th>
                            <th><span>Приоритет</span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <? foreach($webhooks as $webhook) :?>
                        <tr>
                            <td>
                                <a href="#"><?=$webhook->mlg_id?></a>
                            </td>
                            <td>
                                <?=$webhook->themeValue->name?>
                            </td>
                            <td>
                                <?=$webhook->author_name?>
                            </td>
                            <td>
                                <?=$webhook->post_content?>
                            </td>
                            <td>
                                <?=$webhook->locationValue->name?>
                            </td>
                            <td>
                                <?=$webhook->categoryValue->name?>
                            </td>
                            <td>
                                <?=$webhook->priorityValue->name?>
                            </td>

                        </tr>
                        <? endforeach; ?>
                        </tbody>
                    </table>
                </div>
<!--                <ul class="pagination pull-right">-->
<!--                    <li><a href="#"><i class="fa fa-chevron-left"></i></a></li>-->
<!--                    <li><a href="#">1</a></li>-->
<!--                    <li><a href="#">2</a></li>-->
<!--                    <li><a href="#">3</a></li>-->
<!--                    <li><a href="#">4</a></li>-->
<!--                    <li><a href="#">5</a></li>-->
<!--                    <li><a href="#"><i class="fa fa-chevron-right"></i></a></li>-->
<!--                </ul>-->
            </div>
        </div>
    </div>
</div>
