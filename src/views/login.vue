<template>
  <el-form
    status-icon
    ref="form"
    label-width="80px"
    class="login"
    autocomplete="off"
    :model="form"
    :rules="rules"
  >
    <h2>会员登录</h2>
    <el-form-item label="用户名" prop="username">
      <el-input type="text" v-model.trim="form.username"></el-input>
    </el-form-item>
    <el-form-item label="密码" prop="password">
      <el-input type="password" v-model.trim="form.password"></el-input>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="handleLogin">登录</el-button>
      <el-button type="danger">注册</el-button>
    </el-form-item>
  </el-form>
</template>

<script>
export default {
  name: "login",
  data() {
    return {
      form: {
        username: null,
        password: null
      },
      rules: {
        username: [
          { required: true, message: "请输入用户名！", trigger: "blur" }
        ],
        password: [{ required: true, message: "请输入密码！", trigger: "blur" }]
      }
    };
  },
  methods: {
    handleLogin() {
      this.$refs.form &&
        this.$refs.form.validate(vali => {
          if (vali) {
            const { username, password } = this.form;
            const param = new FormData();
            param.append("username", username);
            param.append("password", password);
            this.$http.post("/api/admin/login.php", param).then(res => {
              if (res.data.code === 20000) {
                localStorage.setItem('sun_token',res.data.token);
                this.$router.push("/");
              } else {
                this.$message.error(res.data.desc);
              }
            });
          }
        });
    }
  }
};
</script>
<style lang="less" scoped>
.login {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 400px;
  border-radius: 4px;
  box-shadow: 0 0 16px rgba(0, 0, 0, 0.15);
  padding: 30px 20px;
  border: 1px solid #f0f0f0;
  background-color: #fff;
  h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
  }
}
</style>