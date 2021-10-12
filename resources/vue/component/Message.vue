<template>
    <div v-show="isShow"  class="notification"><p>{{text}}</p></div>
</template>




<script>
export default {
    name: "Message",
    data() {
         return {
            isShow: false,
            text: ''
        };
    },
}



</script>


<style>

.notification {
    background: #fd9843;
    border-radius: 5px;
    padding: 15px;
    position: absolute;
    right: 50%;
    top: 50px;
    z-index: 9999;
    max-width: 276px;
    left: 50%;
    margin-right: -50%;
    transform: translate(-50%, 0%);
    display: inherit !important; /* override v-show display: none */
    transition: opacity 0.5s;
}

.notification p {
    font-weight: 500;
    font-size: 14px;

    color: #fff;
    line-height: 19px;

    text-align: center;
}


.notification[style*="display: none;"] {
    opacity: 0;
    pointer-events: none; /* disable user interaction */
    user-select: none; /* disable user selection */
}


@media(max-width: 768px) {
    .notification p {
        line-height: 14px;
        font-size: 12px;
    }
}

@media (max-width: 576px) {
    .notification {
        width: 247px;
        top: 78px;
    }
}
</style>
