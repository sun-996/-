<template>
  <div class="panel">
    <div class="panel-title">
      <h2>用户列表</h2>
      <el-breadcrumb separator="/">
        <el-breadcrumb-item to="/">首页</el-breadcrumb-item>
        <el-breadcrumb-item to="/admin">用户管理</el-breadcrumb-item>
        <el-breadcrumb-item>导航列表</el-breadcrumb-item>
      </el-breadcrumb>
    </div>
    <div class="panel-btns">
      <el-button type="primary" size="mini" icon="el-icon-plus" @click="addForm">新建</el-button>
      <el-button size="mini" v-show="ids.length" @click="delBatch">批量删除</el-button>
    </div>

    <el-table
      stripe
      ref="multipleTable"
      :data="tableData"
      tooltip-effect="dark"
      style="width: 100%"
      @selection-change="checkAll"
    >
      <el-table-column type="selection" width="55"></el-table-column>
      <el-table-column label="头像" width="160">
        <template slot-scope="{row}">
          <el-avatar :size="24" :src="row.avatar" />
        </template>
      </el-table-column>
      <el-table-column prop="username" label="用户名"></el-table-column>
      <el-table-column prop="realname" label="真实姓名"></el-table-column>
      <el-table-column prop="regtime" label="注册时间" width="160"></el-table-column>
      <el-table-column label="审核" width="80">
        <template slot-scope="{row}">
          <el-switch
            v-model="row.state"
            active-value="1"
            inactive-value="0"
            active-color="#13ce66"
            @change="stateChange(row.state,row.id)"
          />
        </template>
      </el-table-column>
      <el-table-column label="操作" width="160">
        <template slot-scope="{row}">
          <el-button size="mini" @click="editForm(row)">修改</el-button>

          <el-button size="mini" type="danger" @click="delItem(row.id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog :title="flag+'用户'" :visible.sync="show" width="500px" :close-on-click-modal="false">
      <el-form
        ref="form"
        :model="form"
        :rules="rules"
        autocomplete="off"
        label-width="100px"
        status-icon
      >
        <el-form-item label="用户名" prop="username">
          <el-input v-model.trim="form.username"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input v-model.trim="form.password" type="password" :placeholder="flag=='修改' ?'不修改则为空':''"></el-input>
        </el-form-item>
        <el-form-item label="确认密码" prop="repassword">
          <el-input v-model.trim="form.repassword" type="password"></el-input>
        </el-form-item>
        <el-form-item label="真实姓名" prop="realname">
          <el-input v-model.trim="form.realname"></el-input>
        </el-form-item>
        <el-form-item label="上传头像" prop="avatar">
          <el-upload
            class="avatar-uploader"
            action="/api/upload.php"
            name="upfile"
            :show-file-list="false"
            :on-success="handleAvatarSuccess"
            :before-upload="beforeAvatarUpload"
          >
            <img v-if="form.avatar" :src="form.avatar" class="avatar" />
            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
          </el-upload>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="show = false">取 消</el-button>
        <el-button type="primary" @click="saveForm">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
const fileds = {
  username: "",
  password: "",
  repassword: "",
  realname: "",
  avatar: ""
};
export default {
  data() {
    const validatePass = (rule, value, callback) => {
      if (this.form.password !== "" && value !== this.form.password) {
          callback(new Error("两次输入密码不一致!"));
      }
      callback();
    };
    return {
      tableData: [],
      ids: [],
      show: false,
      form: { ...fileds },
      flag: "添加",
      rules: {
        username: [
          { required: true, message: "请输入用户名", trigger: "blur" }
        ],
        password: [
          { required: true, message: "请输入密码", trigger: "blur" }
        ],
        repassword: [
          { required: true, message: "请再次密码", trigger: "blur" },
          {validator: validatePass,trigger: "blur"}
        ],
        realname: [
          { required: true, message: "请输入真实姓名", trigger: "blur" }
        ]
      }
    };
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$http.get("/api/admin/list.php").then(res => {
        this.tableData = res.data.record;
      });
    },
    handleDel(id) {
      let isBatch = Array.isArray(id);
      let param = new FormData();
      param.append("id", isBatch ? this.ids.join(",") : id);
      this.$http.post("/api/admin/del.php", param).then(res => {
        if (res.data.code === 20000) {
          this.tableData = this.tableData.filter(item => {
            if (isBatch) {
              return this.ids.indexOf(item.id) == -1;
            } else {
              return item.id != id;
            }
          });
        } else {
          this.$message.error(res.data.desc);
        }
      });
    },
    checkAll(val) {
      this.ids = val.map(item => item.id);
    },
    delItem(id) {
      this.$confirm("此操作将永久删除该文件, 是否继续?", "提示")
        .then(_ => {
          this.handleDel(id);
        })
        .catch(e => {});
    },
    delBatch() {
      this.$confirm("您确定要删除所选择的行吗?", "提示")
        .then(_ => {
          this.handleDel(this.ids);
        })
        .catch(e => {});
    },
    addForm() {
      this.show = true;
      this.flag = "添加";
      this.rules.password[0].required = true;
      this.rules.repassword[0].required = true;
      this.$refs.form && this.$refs.form.clearValidate();
      this.form = { id: new Date().getTime(), ...fileds };
    },
    saveForm() {
      this.$refs.form &&
        this.$refs.form.validate(vali => {
          if (vali) {
            this.show = false;
            const form = { ...this.form };

            //虚拟表单对象
            const param = new FormData();
            for (let key in form) {
              param.append(key, form[key]);
            }

            //添加和修改处理
            let url = {
              添加: "/api/admin/add.php",
              修改: "/api/admin/edit.php"
            };

            this.$http.post(url[this.flag], param).then(res => {
              if (res.data.code === 20000) {
                if (this.flag == "添加") {
                  this.tableData.push(form);
                } else if (this.flag == "修改") {
                  this.tableData = this.tableData.map(item => {
                    return item.id == form.id ? form : item;
                  });
                }
                this.fetch();
              } else {
                this.$message.error(res.data.desc);
              }
            });
          }
        });
    },
    editForm(row) {
      this.show = true;
      this.flag = "修改";
      this.$refs.form && this.$refs.form.clearValidate();
      this.rules.password[0].required = false;
      this.rules.repassword[0].required = false;
      this.form = { ...row, password: "" };
    },
    stateChange(state, id) {
      let param = new FormData();
      param.append("state", state);
      param.append("id", id);
      this.$http.post("/api/admin/state.php", param).then(res => {
        if (res.data.code !== 20000) {
          this.$message.error(res.data.desc);
        }
      });
    },
    handleAvatarSuccess(res, file) {
      this.form.avatar = res.filepath;
    },
    beforeAvatarUpload(file) {
      const allowExt = ["jpeg", "jpg", "gif", "png"];
      const ext = file.name.slice(file.name.lastIndexOf(".") + 1);
      const isAllowExt = allowExt.includes(ext);

      const isLt2M = file.size / 1024 / 1024 < 2;

      if (!isAllowExt) {
        this.$message.error(
          "上传头像图片只能是 " + allowExt.toString() + " 格式!"
        );
      }
      if (!isLt2M) {
        this.$message.error("上传头像图片大小不能超过 2MB!");
      }
      return isAllowExt && isLt2M;
    }
  }
};
</script>

<style lang="less">
.avatar-uploader .el-upload {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.avatar-uploader .el-upload:hover {
  border-color: #409eff;
}
.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 80px;
  height: 80px;
  line-height: 80px;
  text-align: center;
}
.avatar {
  width: 80px;
  height: 80px;
  display: block;
}
</style>

<style lang="less" scoped>
.panel-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
}
.panel-btns {
  padding: 10px;
}
</style>