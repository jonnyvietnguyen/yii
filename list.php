    <?php

    use kartik\helpers\Html;
    use kartik\grid\GridView;
    use kartik\widgets\ActiveForm;

    /* @var $this kartik\web\View */
    /* @var $searchModel app\models\HistorySearch */
    /* @var $dataProvider yii\data\ActiveDataProvider */

    $this->title = 'Nội dung cho kịch bản: ' . $model->script_name;
    $this->params['breadcrumbs'][] = ['label' => 'Scripts', 'url' => ['index']];
    $this->params['breadcrumbs'][] = ['label' => $model->script_name];
    $this->params['breadcrumbs'][] = 'Danh sách nội dung';

    ?>
    <div class="container">
    <?php $form = ActiveForm::begin(['options' => ['method' => 'post'], 'action' => \yii\helpers\Url::to(["script/list-view"]) ]); ?>
        <input id="form-token" type="hidden" name="<?=Yii::$app->request->csrfParam?>" value="<?=Yii::$app->request->csrfToken?>"/>
        <div class="history-index">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="cf nestable-lists">
                <div class="dd" id="nestable">
                    <ol class="dd-list">
                        <?php foreach ($dataProvider->getModels() as $key => $item) { ?>
                            <li class="dd-item" data-id="<?= $item['id'] ?>">
                                <input type="checkbox" class="item-checkbox" name="selection[]" value="<?= $item['id'] ?>" style="float: left; margin:10px 10px">
                                <div class="dd-handle item-content"><?= $item['content'] ?></div>
                            </li>
                        <?php }?>
                    </ol>
                </div>
                <input type="hidden" name="id" value="<?=$id?>">
                <input type="hidden" name="type" value="<?=$type?>">
                <textarea id="nestable-output" name="output"></textarea>
            </div>

        </div>
        <div>
            <button type="button" class="btn btn-primary" id="addScript"> Loại bỏ</button>
            <?= Html::submitButton('Cập nhật', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name'=>'subm']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script>

        $(document).ready(function()
        {

            var updateOutput = function(e)
            {
                var list   = e.length ? e : $(e.target),
                    output = list.data('output');
                if (window.JSON) {
                    output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
                } else {
                    output.val('JSON browser support required for this demo.');
                }
            };

            // activate Nestable for list 1
            $('#nestable').nestable({
                group: 1
            })
                .on('change', updateOutput);


            // output initial serialised data
            updateOutput($('#nestable').data('output', $('#nestable-output')));

        });
    </script>
    <script>
        $(document).ready(function () {
            $("#addScript").click(function () {
                var selected = [];
                $('.item-checkbox').each(function() {
                    if ($(this).is(":checked")) {
                        selected.push($(this).attr('value'));
                    }
                });
                $.ajax({
                    type: 'POST',
                    async: false,
                    url: '<?=Yii::$app->homeUrl?>?r=script/remove-script',
                    data: {selected:selected,id:'<?=$id?>',type:'<?=$type?>'},
                    dataType: 'json',
                    success: function (response) {
                        if(response.code ==0){
                            location.reload();
                        }else {
                            alert(response.message);
                        }
                    },
                    error: function (response) {
                        alert('Có lỗi. Vui lòng liên hệ bạn quản trị');
                    }
                });
            });
        })
    </script>
