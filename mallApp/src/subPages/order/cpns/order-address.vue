<template>
  <view class="order-address" @click="handleOpenClick">
    <template v-if="address">
      <view class="address-region">{{
        address?.addressRegionText.replace(/\//g, "")
      }}</view>
      <view class="address-detail">
        {{ address?.addressDetail }}
      </view>
      <view class="address-user-info">
        <text class="text">
          {{ address?.addressName }}
        </text>
        <text>
          {{ address?.addressPhone }}
        </text>
      </view>
    </template>
    <template v-else>请先填写收货地址</template>
    <view class="icon">
      <uni-icons type="right" size="40rpx"></uni-icons>
    </view>
  </view>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useMyStore } from "@/store/my";

const myStore = useMyStore();
const address = computed(() => {
  return myStore.address;
});

function handleOpenClick() {
  uni.navigateTo({
    url: "/subPages/receivingAddress/receivingAddress",
  });
}
</script>

<style lang="scss" scoped>
.order-address {
  padding: 30rpx;
  position: relative;
  background-color: #fff;
  border-radius: 30rpx;
  margin-bottom: 30rpx;
  font-size: $gFontSize;
  .address-region {
    margin-bottom: 10rpx;
  }
  .address-detail {
    font-size: 36rpx;
    font-weight: bold;
    margin-bottom: 10rpx;
  }
  .address-user-info {
    .text {
      margin-right: 20rpx;
    }
  }
  .icon {
    position: absolute;
    top: 50%;
    right: 20rpx;
    transform: translateY(-50%);
  }
}
</style>
