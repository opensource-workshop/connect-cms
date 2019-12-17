<template>
    <div id="overlay" v-on:click="clickEvent">
        <div id="content">
          <p><slot></slot></p>
          <button v-on:click="clickEvent">close</button>
        </div>
    </div>
</template>

<script>
    export default {
        methods :{
            clickEvent: function(){
                this.$emit('from-child')
            }
        }
    }
</script>
<style>
#content{
  z-index:10;
  width:50%;
  padding: 1em;
  background:#fff;
}

#overlay{
  /*　要素を重ねた時の順番　*/

  z-index:1;

  /*　画面全体を覆う設定　*/
  position:fixed;
  top:0;
  left:0;
  width:100%;
  height:100%;
  background-color:rgba(0,0,0,0.5);

  /*　画面の中央に要素を表示させる設定　*/
  display: flex;
  align-items: center;
  justify-content: center;

}
</style>