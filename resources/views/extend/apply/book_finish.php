<md-content ng-cloak layout="column" ng-controller="book" layout-align="start center">
<div ng-include="'stepsTemplate'"></div>
<div style="width:960px">
    <md-card style="width: 100%">
        <md-card-header md-colors="{background: 'indigo'}">
            <md-card-header-text>
                <span class="md-title">完成的加掛問卷</span>
            </md-card-header-text>
        </md-card-header>
        <md-content>
            <md-list>
                <survey-page ng-repeat="(key,page) in pages" page="page" index="$index"></survey-page>
            </md-list>
        </md-content>
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
                    <td>{{translateQustionType(question)}}</td>
                    <td>{{question.id}}</td>
                    <td>
                        <span ng-repeat="(key,answer) in question.node.answers">
                            {{key+1}}.{{answer.title}}<br>
                        </span>
                        <span ng-if="checkType(question.node.type)">{{question.title}}</span>
                    </td>
                </tr>
            </table>
            <md-divider></md-divider>
        <div>
        `,
        controller: function($scope){
            $scope.questionAnalysis = function(page){
                var browser_page = page;
                var field = [];
                var question_number = 1;
                var answer_number = 1;

                for(var i=0; i<browser_page.length; i++){
                    browser_page[i] = $scope.getQuestionTitle(browser_page[i]);
                    if(field.indexOf(browser_page[i].node.id)==-1){
                        field.push(browser_page[i].node.id);
                        browser_page[i].question_number = question_number++;
                        browser_page[i].answer_number = answer_number;
                    }else if(browser_page[i].node.type=='scale'){
                        field.push(browser_page[i].node.id);
                        browser_page[i].question_number = question_number;
                    }
                    else{
                        browser_page[i].answer_number = ++answer_number;
                        browser_page[i].rowspan = 0;
                        continue;
                    }
                    browser_page[i].rowspan = 0;
                    for(var x=i; x<page.length; x++){
                        if(browser_page[i].node.id==page[x].node.id){
                            if(browser_page[i].node.type == "scale"){
                                browser_page[i].rowspan = 1;
                            }else{
                                browser_page[i].rowspan++;
                            }
                        }
                    }
                }
                return $scope.page = browser_page;
            }

            $scope.translateQustionType = function(question){
                switch(question.node.type){
                    case "radio":
                    return "單選題";
                    break;

                    case "text":
                    return "文字填答";
                    break;

                    case "scale":
                    return "量表題";
                    break;

                    case "checkbox":
                    return "複選題";
                    break;

                    case "select":
                    return "下拉式選單";
                    break;

                    case "number":
                    return "數子題";
                    break;

                    case "explain":
                    return "說明文字";
                    break;
                }
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
                return question;
            }
            $scope.questionAnalysis($scope.page);
        }
    }
})


</script>