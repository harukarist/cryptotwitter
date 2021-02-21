<template>
  <transition
    name="modal"
    appear>
    <div
      class="c-modal__wrapper"
      @click.self="$emit('close')">
      <div class="c-modal__dialog">
        <div class="c-modal__header">
          <i
            class="fas fa-times c-modal__close"
            @click="$emit('close')" />
        </div>
        <div class="c-modal__body">
          <slot name="text" />
        </div>
        <div class="c-modal__footer">
          <slot name="btn" />
          <button
            class="c-btn--main c-modal__footer-btn"
            @click="$emit('close')">
            取り消し
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>
