<template>
  <transition name="modal" appear>
    <div class="c-modal__wrapper" @click.self="$emit('close')">
      <div class="c-modal__dialog">
        <div class="c-modal__header">
          <i class="fas fa-times c-modal__close" @click="$emit('close')"></i>
        </div>
        <div class="c-modal__body">
          <slot name="text"></slot>
        </div>
        <div class="c-modal__footer">
          <slot name="btn"></slot>
          <button class="c-btn--main c-modal__footer-btn" @click="$emit('close')">
            取り消し
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>
