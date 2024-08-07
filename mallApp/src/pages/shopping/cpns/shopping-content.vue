<template>
  <view class="shopping-content">
    <template v-for="(item, index) in shoppingList" :key="item.shoppingId">
      <view class="shopping-item-box">
        <view
          class="shopping-item"
          :style="`transform: translateX(${endX[index]}px)`"
          @touchstart.passive="handleTouchStart($event, index)"
          @touchmove.passive="handleTouchMove($event, index)"
          @touchend.passive="handleTouchEnd($event, index)"
        >
          <view class="check-box" @click="handleChangeCheckedClick(item)">
            <uni-icons
              :type="item.isChecked ? 'checkbox-filled' : 'circle'"
              size="48rpx"
              :color="item.isChecked ? '#f3402b' : '#7e7e7e'"
            ></uni-icons>
          </view>
          <view class="image-box">
            <!-- #ifndef H5  -->
            <image
              class="image"
              :src="item.good.goodImg"
              :lazy-load="true"
              mode="widthFix"
            ></image>
            <!-- #endif  -->
            <!-- #ifdef H5  -->
            <img class="image" v-lazy="item.good.goodImg" />
            <!-- #endif -->
          </view>
          <view class="item-info">
            <view class="item-name">{{ item.good.goodName }}</view>
            <view class="item-type" @click="handkeChangeTypeClick(item, index)"
              >{{ item.good.type[item.type] + " " + item.good.color[item.color] }}
              <uni-icons type="down" size="28rpx" color="#7e7e7e"></uni-icons>
            </view>
            <view class="item-price">
              <text class="text">￥{{ item.good.price[item.type] }}</text>
              <numEdit
                v-model="item.num"
                @change="$emit('changeNum', $event, item, index)"
              ></numEdit>
            </view>
          </view>
          <view class="option" @click="handleDeleteClick(item, index)"> 删除 </view>
        </view>
      </view>
    </template>
    <template v-if="shoppingList.length === 0">
      <view class="cart-null">
        <image class="image" src="@/static/cartnull.png" mode="widthFix"></image>
        <view class="text">购物车还是空的</view>
        <view class="navigator" @click="handleCasualClick">随便看看</view>
      </view>
    </template>
    <detailBuy
      v-if="changeType"
      ref="detailBuyRef"
      @close="handleCloseClick"
      @load="handleLoadClick"
      :hiddenNum="true"
      :detail="detail"
    ></detailBuy>
  </view>
</template>

<script setup lang="ts">
import { reactive, getCurrentInstance, watch, ref } from "vue";
import numEdit from "@/components/num-edit.vue";
import detailBuy from "@/pages/detail/cpns/detail-buy.vue";

import { deepClone } from "@/utils";

const instance = getCurrentInstance();
const props = defineProps(["shoppingList", "isAllChecked"]);
const emit = defineEmits(["changeNum", "delete", "changeChecked", "close"]);
const shoppingList = reactive([] as any);
const changeType = ref(false);
const detail = ref({} as any);
const detailBuyRef = ref();
const changeIndex = ref(-1);
const changeItem = ref({} as any);
//传递购物车列表
watch(
  () => props.shoppingList,
  (newVal) => {
    shoppingList.length = 0;
    const data = deepClone(newVal);
    shoppingList.push(
      ...data.map((item: any) => {
        item.good.price = item.good.price.split(",");
        item.good.type = item.good.type.split(",");
        item.good.color = item.good.color.split(",");
        return item;
      })
    );
  },
  { deep: true }
);
//是否全选,给购物车check赋值 2为了能够监听而出现
watch(
  () => props.isAllChecked,
  (newVal) => {
    if (newVal === 2) return;
    shoppingList.forEach((item: any) => {
      item.isChecked = newVal;
    });
  },
  {
    immediate: true,
  }
);
let startX = 0,
  lastX = 0,
  elementPosition = 0;
// 存储滑动距离
const endX = reactive([] as any[]);
// 触摸前
function handleTouchStart(e: any, index: any) {
  endX[index] = endX[index] ?? 0;
  startX = e.changedTouches[0].clientX - endX[index];
  if (elementPosition === 0) {
    getElementPosition();
  }
}
// 触摸中
function handleTouchMove(e: any, index: any) {
  lastX = e.changedTouches[0].clientX;
  const disX = lastX - startX;
  //最多右滑的距离
  if (disX < elementPosition - 40) return;
  //最多左滑的距离
  if (disX > 0 && disX > elementPosition) return;
  endX[index] = disX;
}
//触摸结束
function handleTouchEnd(e: any, index: any) {
  const threshold = elementPosition / 2;
  if (endX[index] > threshold) {
    endX[index] = 0;
  } else if (endX[index] && endX[index] !== 0) {
    endX[index] = elementPosition;
  }
  startX = 0;
  lastX = 0;
}
//加载删除盒子需要transForm多少px
function getElementPosition() {
  uni
    .createSelectorQuery()
    .in(instance)
    .select(".option")
    .boundingClientRect((data: any) => {
      elementPosition = -(data.right - data.left);
    })
    .exec();
}
//改变check状态
function handleChangeCheckedClick(item: any) {
  item.isChecked = !item.isChecked;
  emit("changeChecked", item);
}
// 删除购物车
function handleDeleteClick(item: any, index: any) {
  uni.showModal({
    title: "",
    content: "是否删除",
    success: function (res) {
      if (res.confirm) {
        emit("delete", item);
        endX.splice(index, 1);
        shoppingList.splice(index, 1);
      }
    },
  });
}
//随便看看
function handleCasualClick() {
  uni.switchTab({
    url: "/pages/home/home",
  });
}

//改变购物车数量 颜色这些
function handkeChangeTypeClick(item: any, index: any) {
  changeIndex.value = index;
  changeItem.value = item;
  detail.value = { ...item.good, shoppingId: item.shoppingId };
  changeType.value = true;
}
function handleLoadClick() {
  const { color, type } = changeItem.value;
  detailBuyRef.value &&
    detailBuyRef.value.loadData({
      colorIndex: Number(color),
      typeIndex: Number(type),
    });
}
function handleCloseClick(data: any) {
  if (!data) {
    changeType.value = false;
    return;
  }
  const { colorIndex: color, typeIndex: type } = data;
  const localShoppingList = { ...shoppingList[changeIndex.value] };
  Object.assign(shoppingList[changeIndex.value], { color, type });
  const oldPrice = parseFloat(localShoppingList.good.price[localShoppingList.type]);
  const newPrice = parseFloat(localShoppingList.good.price[type]);
  emit("close", localShoppingList.isChecked, newPrice - oldPrice);
  changeIndex.value = -1;
  changeType.value = false;
}
</script>

<style lang="scss" scoped>
$darkBgColor: darken(
  $color: $gBgLightColor,
  $amount: 10,
);
.shopping-content {
  .cart-null {
    @include normalAbsolute();
    text-align: center;
    .image {
      width: 200rpx;
      margin: auto;
    }
    .text {
      margin: 40rpx 0;
      color: #b7b7b7;
    }
    .navigator {
      width: fit-content;
      margin: auto;
      padding: 20rpx 60rpx;
      color: #8c8c8c;
      border: 1px solid #b9b9b9;
      border-radius: 9999rpx;
    }
  }
  .shopping-item-box {
    margin: 20rpx;
    padding: 20rpx;
    background-color: white;
    border-radius: 20rpx;
    box-sizing: border-box;
    overflow: hidden;
    transition: all 0.5s;
    .shopping-item {
      @include normalFlex(row, flex-start);
      & > view {
        flex-shrink: 0;
      }
      .check-box {
        width: 10%;
      }
      .image-box {
        width: 24%;
        margin: 0 4% 0 2%;
        background-color: $gBgLightColor;
        border-radius: 20rpx;
      }
      .item-info {
        width: 60%;
        @include normalFlex(column, space-between, flex-start);
        .item-type {
          margin: 20rpx 0;
          padding: 6rpx 10rpx 6rpx 20rpx;
          text-align: center;
          color: #7e7e7e;
          font-size: $gFontSize;
          background-color: $gBgLightColor;
          border-radius: 10rpx;
        }
        .item-price {
          width: 100%;
          @include normalFlex(row, space-between);
          & > .text {
            color: $gPriceColor;
          }
          :deep(.num-edit) {
            border: 1px solid $darkBgColor;
            border-radius: 9999rpx;
            .reduce,
            .add {
              font-size: 28rpx !important;
              margin: 4rpx 18rpx;
              font-weight: bold;
            }
            .reduce {
              width: 20rpx;
              height: 4rpx;
            }
            .num {
              padding: 0 20rpx;
              border-left: 1px solid $darkBgColor;
              border-right: 1px solid $darkBgColor;
              background-color: transparent;
            }
          }
        }
      }
      .option {
        width: 15%;
        @include normalFlex();
        align-self: stretch;
        margin-left: 30rpx;
        font-size: $gFontSize;
        color: white;
        background-color: $gPriceColor;
      }
    }
  }
}
</style>
