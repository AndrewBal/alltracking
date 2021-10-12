<template>
    <transition name="fade">
        <div id="carrier-choice" class="modal" v-if="show">
            <div class="modal__backdrop" @click="closeModal()"/>

            <div class="modal__dialog   ">
                <div class="modal-header">
                    <slot name="header"/>
                    <button type="button" class="uk-modal-close-default uk-icon uk-close" @click="closeModal()">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.97034 0.968875C1.04001 0.899031 1.12277 0.843616 1.21389 0.805806C1.30501 0.767997 1.40269 0.748535 1.50134 0.748535C1.59999 0.748535 1.69767 0.767997 1.78879 0.805806C1.87991 0.843616 1.96267 0.899031 2.03234 0.968875L6.00134 4.93938L9.97034 0.968875C10.0401 0.899143 10.1229 0.843829 10.214 0.80609C10.3051 0.768352 10.4027 0.748928 10.5013 0.748928C10.6 0.748928 10.6976 0.768352 10.7887 0.80609C10.8798 0.843829 10.9626 0.899143 11.0323 0.968875C11.1021 1.03861 11.1574 1.12139 11.1951 1.2125C11.2329 1.30361 11.2523 1.40126 11.2523 1.49988C11.2523 1.59849 11.2329 1.69614 11.1951 1.78725C11.1574 1.87836 11.1021 1.96114 11.0323 2.03088L7.06184 5.99988L11.0323 9.96887C11.1021 10.0386 11.1574 10.1214 11.1951 10.2125C11.2329 10.3036 11.2523 10.4013 11.2523 10.4999C11.2523 10.5985 11.2329 10.6961 11.1951 10.7873C11.1574 10.8784 11.1021 10.9611 11.0323 11.0309C10.9626 11.1006 10.8798 11.1559 10.7887 11.1937C10.6976 11.2314 10.6 11.2508 10.5013 11.2508C10.4027 11.2508 10.3051 11.2314 10.214 11.1937C10.1229 11.1559 10.0401 11.1006 9.97034 11.0309L6.00134 7.06037L2.03234 11.0309C1.96261 11.1006 1.87982 11.1559 1.78872 11.1937C1.69761 11.2314 1.59996 11.2508 1.50134 11.2508C1.40272 11.2508 1.30507 11.2314 1.21396 11.1937C1.12286 11.1559 1.04007 11.1006 0.97034 11.0309C0.900608 10.9611 0.845294 10.8784 0.807555 10.7873C0.769817 10.6961 0.750393 10.5985 0.750393 10.4999C0.750393 10.4013 0.769817 10.3036 0.807555 10.2125C0.845294 10.1214 0.900608 10.0386 0.97034 9.96887L4.94084 5.99988L0.97034 2.03088C0.900495 1.96121 0.845081 1.87844 0.807271 1.78733C0.769462 1.69621 0.75 1.59853 0.75 1.49988C0.75 1.40122 0.769462 1.30354 0.807271 1.21242C0.845081 1.12131 0.900495 1.03854 0.97034 0.968875Z" fill="white"/>
                        </svg>

                    </button>
                </div>

                <div class="modal__body">
                    <slot name="body"/>
                </div>


            </div>
        </div>
    </transition>
</template>

<script>

export default {
    name: "Modal",

    data() {
        return {
            show: false,

        };
    },

    methods: {
        closeModal() {
            this.show = false;
            document.querySelector("body").classList.remove("overflow-hidden");
        },
        openModal() {
            this.show = true;
            document.querySelector("body").classList.add("overflow-hidden");

            this.$emit('openmodal', null);

        }
    }
};
</script>


<style lang="scss" scoped>
.modal {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    overflow-x: hidden;
    overflow-y: auto;

    &__backdrop {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-color: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    &__dialog {
        position: relative;
        width: 600px;
        background-color: #ffffff;
        border-radius: 5px;
        margin: 50px auto;
        display: flex;
        flex-direction: column;
        z-index: 2;
        @media screen and (max-width: 992px) {
            width: 90%;
        }
    }
    &__close {
        width: 30px;
        height: 30px;
    }
    &__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }
    &__body {
        padding-bottom: 30px;
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter,
.fade-leave-to {
    opacity: 0;
}

@media (max-width: 768px) {
    .modal, .modal__backdrop {

        top: 90px;
    }
}
</style>
