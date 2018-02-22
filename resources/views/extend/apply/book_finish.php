<md-content ng-cloak layout="column" ng-controller="book" layout-align="start center">
<div ng-include="'stepsTemplate'"></div>
<div style="width:960px">
    <md-card style="width: 100%">
        <md-card-header md-colors="{background: 'indigo'}">
            <md-card-header-text>
                <span class="md-title">完成的加掛問卷</span>
            </md-card-header-text>
        </md-card-header>
        <md-card-content>
            <survey-page ng-repeat="(key,page) in pages" page="page" index="$index"></survey-page>
        </md-card-content>
        <md-card-actions layout="row">
            <md-button class="md-raised md-primary" ng-click="changeStep('preStep')" style="width: 50%;height: 50px;font-size: 18px">返回編輯問卷</md-button>
            <md-button class="md-raised md-primary" ng-click="changeStep('nextStep')" style="width: 50%;height: 50px;font-size: 18px">下一步</md-button>
        </md-card-actions>
    </md-card>
</div>
</md-content>
<style>
</style>
<script>
app.controller('book', function ($scope, $http, $sce){
    $scope.getBookFinishQuestions = function() {
        $http({method: 'POST', url: 'getBookFinishQuestions', data:{}})
        .success(function(data, status, headers, config) {
            $scope.pages = data;
        })
        .error(function(e){
            console.log(e);
        });
    }
    $scope.getBookFinishQuestions();
    $scope.trustAsHtml = function(string) {
        return $sce.trustAsHtml(string);
    };

    $scope.changeStep = function(method) {
        $http({method: 'POST', url: method, data:{}})
        .success(function(data, status, headers, config) {
            location.reload();
        })
        .error(function(e){
            console.log(e);
        });
    }
});
app.directive('surveyPage', function(){
    return {
        restrict:'E',
        scope:{
            page:'=',
            index: '='
        },
        template:`
        <div style="margin:20px">
            <h3>第{{index+1}}頁</h3>
            <table class="ui very compact celled table" style="font-family: Microsoft JhengHei">
                <tr style="font-size: 18px;">
                    <td><b>項目</b></td>
                    <td><b>題目</b></td>
                    <td><b>題型</b></td>
                    <td><b>題目代號</b></td>
                    <td><b>選項</b></td>
                </tr>
                <tr ng-repeat="(key,question) in page">
                    <td ng-if="question.rowspan>=1" rowspan="{{question.rowspan}}">
                        {{question.question_number}}
                    </td>
                    <td ng-if="question.rowspan>=1" rowspan="{{question.rowspan}}"> {{getParentNode(key,question,page)}}{{question.question_title}}</td>
                    <td>{{types[question.node.type]}}</td>
                    <td>{{question.id}}</td>
                    <td>
                        <span ng-repeat="(key,answer) in question.node.answers">
                            {{key+1}}.{{answer.title}}<br>
                        </span>
                        <span ng-if="checkType(question.node.type)">{{question.title}}</span>
                    </td>
                </tr>
            </table>
        <div>
        `,
        controller: function($scope){
            $scope.questionAnalysis = function(){
                var field = [];
                var question_number = 1;
                var answer_number = 1;

                for(var i=0; i<$scope.page.length; i++){
                    $scope.getQuestionTitle($scope.page[i]);
                    if(field.indexOf($scope.page[i].node.id)==-1){
                        field.push($scope.page[i].node.id);
                        $scope.page[i].question_number = question_number++;
                        $scope.page[i].answer_number = answer_number;
                    }else if($scope.page[i].node.type=='scale'){
                        field.push($scope.page[i].node.id);
                        $scope.page[i].question_number = question_number;
                    }
                    else{
                        $scope.page[i].answer_number = ++answer_number;
                        $scope.page[i].rowspan = 0;
                        continue;
                    }
                    $scope.page[i].rowspan = 0;
                    for(var x=i; x<$scope.page.length; x++){
                        if($scope.page[i].node.id==$scope.page[x].node.id){
                            if($scope.page[i].node.type == "scale"){
                                $scope.page[i].rowspan = 1;
                            }else{
                                $scope.page[i].rowspan++;
                            }
                        }
                    }
                }
            }

            $scope.types = {
                'radio': '單選題',
                'text': '文字填答',
                'scale': '量表題',
                'checkbox': '複選題',
                'select': '下拉式選單',
                'number': '數子題',
                'explain': '說明文字',
            }

            $scope.checkParentNodeType = function(question){
                 var question_split = question.node.parent_type.split("\\");
                 return  question_split[question_split.length-1];
            }

            $scope.getParentNode = function(key,question,page){
                var node = {};
                node.parent_id = question.node.parent_id;
                node.parent_type = $scope.checkParentNodeType(question);

                var txt = "(第";
                switch(node.parent_type){
                    case 'Answer':
                    for(var i=key; i>=0; --i){
                        for(var j=0; j<page[i].node.answers.length; j++){
                            if(page[i].node.answers[j].id == node.parent_id){
                                txt += page[i].question_number+"題 選項-";
                                txt += page[i].node.answers[j].title;
                                txt += '子題'
                            }
                        }
                    }
                    break;
                }

                txt += ")";

                if(node.parent_type != "Node") return txt;
            }

            $scope.checkType = function(type){
                switch(type){
                    case 'checkbox':
                        return true;
                        break;
                    case "text":
                        return true;
                        break;
                }
            }

            $scope.getQuestionTitle = function(question){
                question.question_title = [];
                switch(question.node.type){
                    case "radio":
                    question.question_title = question.title;
                    break;
                    case "checkbox":
                    question.question_title = question.node.title;
                    break;
                    case "select":
                    question.question_title = question.title;
                    break;
                    case "scale":
                    question.question_title = question.node.title+"："+question.title;
                    break;
                    case "text":
                    question.question_title = question.node.title;
                    break;
                    case "explain":
                    question.question_title = question.node.title;
                    break;
                    case "number":
                    question.question_title = question.title;
                    break;
                }
            }
            $scope.questionAnalysis();
        }
    }
})


</script>