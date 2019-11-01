<template>
  <el-container>
    <el-aside width="auto">
      <a href="#/" class="logo" :class="{collapse: isCollapse}">
        vivo
        <b>Admin</b>
      </a>
      <el-menu
        :default-active="defaultActive"
        class="sidebar"
        unique-opened
        :collapse="isCollapse"
        router
      >
        <el-submenu index="1">
          <template slot="title">
            <i class="el-icon-menu"></i>
            <span>控制面板</span>
          </template>
          <el-menu-item index="/dashboard">仪表盘</el-menu-item>
          <el-menu-item index="/config">基础设置</el-menu-item>
          <el-menu-item index="1-3">发送设置</el-menu-item>
        </el-submenu>

        <el-submenu index="2">
          <template slot="title">
            <i class="el-icon-position"></i>
            <span>底部导航</span>
          </template>
          <el-menu-item index="/footnav">导航列表</el-menu-item>
        </el-submenu>

        <el-submenu index="3">
          <template slot="title">
            <i class="el-icon-mobile-phone"></i>
            <span>产品管理</span>
          </template>
          <el-menu-item index="/classify">分类列表</el-menu-item>
          <el-menu-item index="/product">产品列表</el-menu-item>
        </el-submenu>

        <el-submenu index="4">
          <template slot="title">
            <i class="el-icon-user"></i>
            <span>用户管理</span>
          </template>
          <el-menu-item index="/admin">用户列表</el-menu-item>
        </el-submenu>
        <el-submenu index="5">
          <template slot="title">
            <i class="el-icon-copy-document"></i>
            <span>幻灯管理</span>
          </template>
          <el-menu-item index="/slide">幻灯列表</el-menu-item>
        </el-submenu>
      </el-menu>
    </el-aside>
    <el-container>
      <el-header class="header">
        <div class="el-header__left">
          <el-button
            :icon="isCollapse ? 'el-icon-s-unfold':'el-icon-s-fold'"
            size="mini"
            class="toggle-btn"
            @click="isCollapse=!isCollapse"
          />
          <el-input
            v-model="keyword"
            class="el-search"
            prefix-icon="el-icon-search"
            placeholder="搜索"
          />
        </div>

        <div class="el-header__right">
          <router-link to="/">
            <i class="el-icon-house"></i>
          </router-link>
          <router-link to="/config">
            <i class="el-icon-setting"></i>
          </router-link>
          <el-badge :value="12" class="notice">
            <router-link to="#/notice">
              <i class="el-icon-bell"></i>
            </router-link>
          </el-badge>

          <el-dropdown trigger="click" @command="handleCommand">
            <span class="el-dropdown-link">
              <el-avatar :size="28" :src="circleUrl" />
              <span>道系</span>
            </span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item icon="el-icon-user" command="/dashboard">个人信息</el-dropdown-item>
              <el-dropdown-item icon="el-icon-setting" command="/config">修改设置</el-dropdown-item>
              <el-dropdown-item icon="el-icon-circle-plus-outline" command="/admin">管理用户</el-dropdown-item>
              <el-dropdown-item icon="el-icon-lock" command="/slide">修改密码</el-dropdown-item>
              <el-dropdown-item icon="el-icon-switch-button" divided command="/logout">退出登录</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </div>
      </el-header>
      <el-main>
        <router-view></router-view>
      </el-main>
    </el-container>
  </el-container>
</template>

<script>
import { watch } from "fs";
export default {
  name: "App",
  data() {
    return {
      isCollapse: false,
      circleUrl:
        "https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png",
      keyword: ""
    };
  },
  computed: {
    defaultActive() {
      return this.$route.path === "/" ? "/dashboard" : this.$route.path;
    }
  },
  methods: {
    handleCommand(command) {
      if (command == "/logout") {
        this.$confirm("您确定要退出登录吗？", "提示")
          .then(res => {
            localStorage.removeItem('sun_token');
            this.$router.push('/login');
          })
          .catch(e => {});
      }else{
        this.$router.push(command);
      }
    }
  }
};
</script>


<style lang="less">
.el-search {
  width: 180px;
  .el-input__inner {
    height: 32px;
    line-height: 32px;
  }
  .el-input__icon {
    line-height: 32px;
  }
}
.notice .el-badge__content {
  transform: translate(60%, -50%);
}
</style>

<style lang="less" scoped>
.logo {
  display: flex;
  height: 60px;
  background-color: #409eff;
  color: white;
  font-size: 20px;
  align-items: center;
  padding-left: 30px;
  b {
    display: inline-block;
    padding-left: 5px;
    font-weight: normal;
  }
  &.collapse {
    padding-left: 0;
    justify-content: center;
    b {
      display: none;
    }
  }
}
.el-header {
  display: flex;
  background-color: white;
  align-items: center;
  box-shadow: 1px 1px 6px rgba(0, 0, 0, 0.05);
  > .el-header__left,
  > .el-header__right {
    display: flex;
    align-items: center;
  }
  > .el-header__right {
    flex-grow: 1;
    justify-content: flex-end;
    [class*="el-icon-"] {
      font-size: 18px;
    }

    a {
      padding: 8px 10px;
    }
  }
}

.el-dropdown-link {
  margin-left: 15px;
  display: flex;
  align-items: center;
  cursor: pointer;
  .el-avatar {
    margin-right: 8px;
  }
}

.sidebar {
  height: calc(100vh - 60px);
  border-right: none;
  &:not(.el-menu--collapse) {
    width: 220px;
  }
}

.toggle-btn {
  padding: 6px 10px;
  font-size: 16px;
  margin-right: 10px;
}
.el-aside {
  box-shadow: 1px 1px 6px rgba(0, 0, 0, 0.05);
  z-index: 999;
}
</style>
