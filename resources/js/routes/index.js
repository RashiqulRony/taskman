import Vue from "vue";
import VueRouter from "vue-router";
Vue.use(VueRouter);
import Index from "../views/project/index";
import addProject from "../views/project/edit";
import Rules from "../views/Rules/index/index";
import Profile from "../views/profile/index";
import ProfileEdit from "../views/profile/edit";
import Notification from "../views/notification/index";
import NotificationSettings from "../views/notification/settings";
import NotificationCreate from "../views/notification/add";
import List from "../views/projectDashboard/list";
import Board from "../views/projectDashboard/board/board";
import SingleTaskDetails from "../views/projectDashboard/SingleTaskDetails";
import OverView from "../views/OverView/OverView";

let routes = [
    {
        path        : "/projects",
        component   : Index,
        name        : 'Project'
    },
    {
        path        : "/project/create",
        component   : addProject,
        name        : "project-create"
    },
    {
        path        : "/project/:uuid/edit",
        component   : addProject,
        name        : "project-edit"
    },
    {
        path        : "/project-dashboard/:projectId/rules",
        component   : Rules,
        name        : "Rules"
    },
    {
        path        : "/project-dashboard/:projectId/overview/:type",
        component   : OverView,
        name        : "Project-OverView"
    },
    {
        path        : "/project-dashboard/:projectId/list/:id",
        component   : List,
        name        : "list-view"
    },
    {
        path        : "/project-dashboard/:projectId/list/:id/task/:task_id",
        component   : SingleTaskDetails,
        name        : "single-task-view"
    },
    {
        path        : "/project-dashboard/:projectId/board/:id",
        component   : Board,
        name        : "board-view"
    },

    {
        path        : "/profile",
        component   : Profile,
        name        : "Profile"
    },
    {
        path        : "/profile/edit",
        component   : ProfileEdit,
        name        : "ProfileEdit"
    },
    {
        path        : "/notification",
        component   : Notification,
        name        : "Notification"
    },
    {
        path        : "/notification-settings",
        component   : NotificationSettings,
        name        : "NotificationSettings"
    },
    {
        path        : "/notifications/create",
        component   : NotificationCreate,
        name        : "NotificationCreate"
    },



];

const router = new VueRouter({
    mode: "history",
    routes
});

export default router;
