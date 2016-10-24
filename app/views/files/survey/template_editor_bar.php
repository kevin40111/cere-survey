<div flex layout="row" layout-align="start center">
    <div>
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="{{(quesTypes | filter:{name: node.type})[0].icon}}"></md-icon>
    </div>
    <div style="margin: 0 0 0 16px">{{(quesTypes | filter:{name: node.type})[0].title}}</div>           

    <span flex></span>

    <div class="ui input" ng-if="node.open.moving">
        <input type="text" ng-model="settedPage" placeholder="輸入移動到的頁數..." />
        <md-button class="md-icon-button no-animate" ng-disabled="node.saving" aria-label="移動到某頁" ng-click="setPage(node, settedPage)">
            <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
        </md-button>
    </div>
    <md-button class="md-icon-button no-animate" ng-disabled="node.saving" aria-label="移動到某頁" ng-click="node.open.moving=!node.open.moving" ng-if="!node.open.moving">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
    </md-button>

    <md-button class="md-icon-button" aria-label="上移" ng-disabled="node.saving" ng-if="!$first" ng-click="moveSort(node, -1)">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-up"></md-icon>
    </md-button>
    <md-button class="md-icon-button" aria-label="下移" ng-disabled="node.saving" ng-if="!$last" ng-click="moveSort(node, 1)">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-down"></md-icon>
    </md-button>
    <md-button class="md-icon-button" aria-label="刪除" ng-disabled="node.saving" ng-click="removeNode(node)">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
    </md-button>    
</div>