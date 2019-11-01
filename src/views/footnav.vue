<template>
  <div class="panel">
    <div class="panel-title">
      <h2>底部导航</h2>
      <el-breadcrumb separator="/">
        <el-breadcrumb-item to="/">首页</el-breadcrumb-item>
        <el-breadcrumb-item to="/footnav">底部管理</el-breadcrumb-item>
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
      :default-sort="{prop:'orderid',order:'ascending'}"
    >
      <el-table-column type="selection" width="55"></el-table-column>
      <el-table-column prop="orderid" label="序号" width="80" sortable></el-table-column>
      <el-table-column prop="name" label="名称" width="160" sortable></el-table-column>
      <el-table-column prop="icon" label="默认图标" width="160"></el-table-column>
      <el-table-column prop="iconed" label="选中图标" width="160"></el-table-column>
      <el-table-column prop="path" label="链接"></el-table-column>
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
    <el-dialog :title="flag+'底部导航'" :visible.sync="show" width="500px">
      <el-form
        ref="form"
        :model="form"
        :rules="rules"
        autocomplete="off"
        label-width="100px"
        status-icon
      >
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name"></el-input>
        </el-form-item>
        <el-form-item label="默认图标" prop="icon">
          <el-input v-model="form.icon"></el-input>
        </el-form-item>
        <el-form-item label="选中图标" prop="iconed">
          <el-input v-model="form.iconed"></el-input>
        </el-form-item>
        <el-form-item label="链接" prop="path">
          <el-input v-model="form.path"></el-input>
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.orderid"></el-input-number>
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
  name: "",
  icon: "",
  iconed: "",
  path: "",
  orderid: ""
};
export default {
  data() {
    return {
      tableData: [],
      ids: [],
      show: false,
      form: { ...fileds },
      flag: "添加",
      rules: {
        name: [
          { required: true, message: "请输入名称", trigger: "blur" },
          { min: 3, max: 5, message: "长度在 3 到 5 个字符", trigger: "blur" }
        ],
        icon: [{ required: true, message: "请输入默认图标", trigger: "blur" }],
        iconed: [
          { required: true, message: "请输入选中图标", trigger: "blur" }
        ],
        path: [{ required: true, message: "请输入链接", trigger: "blur" }]
      }
    };
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$http.get("/api/footnav/list.php").then(res => {
        this.tableData = res.data.record;
      });
    },
    handleDel(id) {
      let isBatch = Array.isArray(id);
      let param = new FormData();
      param.append("id", isBatch ? this.ids.join(",") : id);
      this.$http.post("/api/footnav/del.php", param).then(res => {
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
              添加: "/api/footnav/add.php",
              修改: "/api/footnav/edit.php"
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
      this.form = { ...row };
    },
    stateChange(state, id) {
      let param = new FormData();
      param.append("state", state);
      param.append("id", id);
      this.$http.post("/api/footnav/state.php", param).then(res => {
        if (res.data.code !== 20000) {
          this.$message.error(res.data.desc);
        }
      });
    }
  }
};
</script>

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