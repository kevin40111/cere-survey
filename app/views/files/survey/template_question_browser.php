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
                <span ng-if="question.node.rules[0].expression.length > 0" ng-click="showPassQuestion(question.node.rules)" class="ui left pointing red basic label">
                    {{question.node.rules[0].expression.length}}個跳題條件
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
                    <span ng-if="answer.rules[0].expression.length > 0" ng-click="showPassQuestion(answer.rules)" class="ui left pointing red basic label">
                        {{answer.rules[0].expression.length}}個跳答條件
                    </span>
                    </br>
                </span>
                <span ng-if="!checkQuestionType(question)">
                    {{question.answer_number}}.{{question.title}}
                    <span ng-if="question.rules[0].expression.length > 0" ng-click="showPassQuestion(question.rules)" class="ui left pointing red basic label">
                        {{question.node.rules[0].expression.length}}個跳答條件
                    </span>
                </span> <!-- 只印出複選題選項 -->
            </td>
            <td ng-if="question.page.number > 0" ng-click="checkPageRule(question.node.parent_id)">
                <b>第{{question.page.number}}頁</b>
                <span ng-if="question.page.rule[0].expression.length > 0" ng-click="showPassQuestion(question.page.rule)" class="ui left pointing red basic label">
                        {{question.page.rule[0].expression.length}}個跳頁條件
                </span>
            </td>
            <td ng-if="!(question.page.number > 0)"></td>
        </tr>
    </table>
</div>