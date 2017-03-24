<div class="ui basic segment" ng-cloak style="overflow: auto">
    <table class="ui very compact celled table" style="font-family: Microsoft JhengHei">
        <tr style="font-size: 18px;">
            <td><b>項目</b></td>
            <td><b>題目</b></td>
            <td><b>題目代號</b></td>
            <td><b>題型</b></td>
            <td><b>選項</b></td>
            <td><b>頁數</b></td>
        </tr>

        <tr ng-repeat="(key,question) in questions">
            <!-- 題號 -->
            <td  ng-if="question.rowspan>=1"  rowspan="{{question.rowspan}}" style="text-align: center">
                {{question.question_number}}
            </td>

            <!-- 題目 -->
            <td ng-if="question.rowspan>=1" rowspan="{{question.rowspan}}">
                {{getParentNode(question,key)}}
                {{question.question_title["title"]}}
                <span ng-if="question.node.rule.expressions.length > 0" ng-click="showPassQuestion(question.node)" class="ui left pointing red basic label">
                    {{question.node.rule.expressions.length}}個跳題條件
                </span>
            </td>

            <!-- 題目代號 -->
            <td ng-if="question.rowspan>=1" rowspan="{{question.rowspan}}">
                {{question.id}}
            </td>

            <!-- 題型 -->
            <td  ng-if="(question.rowspan>=1)"  rowspan="{{question.rowspan}}">
                {{translateQustionType(question.node.type)}}
            </td>

            <!-- 選項 -->
            <td>
                <span ng-repeat="(key,answer) in question.node.answers">
                    {{key+1}}.{{answer.title}}
                    <span ng-if="answer.rule.expressions.length > 0" ng-click="showPassQuestion(answer)" class="ui left pointing red basic label">
                        {{answer.rule.expressions.length}}個跳答條件
                    </span>
                    </br>
                </span>
                <span ng-if="!checkQuestionType(question)">
                    {{question.answer_number}}.{{question.title}}
                    <span ng-if="question.rule.expressions.length > 0" ng-click="showPassQuestion(question)" class="ui left pointing red basic label">
                        {{question.node.rule.expressions.length}}個跳答條件
                    </span>
                </span> <!-- 只印出複選題選項 -->
            </td>
            <td ng-if="question.page">
                <b>{{question.page.title}}</b>
                <span ng-if="question.page.rule.expressions.length > 0" ng-click="showPassQuestion(question.page)" class="ui left pointing red basic label">
                        {{question.page.rule.expressions.length}}個跳頁條件
                </span>
            </td>
            <td ng-if="!question.page"></td>
        </tr>
    </table>
</div>