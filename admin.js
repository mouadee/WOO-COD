const { createApp, h, ref } = Vue;
const { default: PrimeVue } = primevue.core;
const { default: Button } = primevue.button;
const { default: InputText } = primevue.inputtext;
const { default: Checkbox } = primevue.checkbox;
const { default: DataTable } = primevue.datatable;
const { default: Column } = primevue.column;

const App = {
    setup() {
        const inputs = ref([
            { id: 1, label: 'الإسم الكامل', name: 'full_name', required: true },
            { id: 2, label: 'رقم الهاتف', name: 'phone', required: true },
            { id: 3, label: 'العنوان', name: 'address', required: true },
            { id: 4, label: 'المدينة', name: 'city', required: true },
        ]);
        const disableRightClick = ref(false);

        const addInput = () => {
            const newId = inputs.value.length ? inputs.value[inputs.value.length - 1].id + 1 : 1;
            inputs.value.push({ id: newId, label: '', name: '', required: false });
        };

        const removeInput = (id) => {
            if (confirm('هل أنت متأكد من حذف هذا الحقل؟')) {
                inputs.value = inputs.value.filter(input => input.id !== id);
            }
        };

        return {
            inputs,
            disableRightClick,
            addInput,
            removeInput,
        };
    },
    render() {
        return h('div', { class: 'p-4' }, [
            h('h2', 'Custom COD Admin Panel'),

            h('section', { class: 'mb-6' }, [
                h('h3', 'Form Inputs'),
                h(DataTable, {
                    value: this.inputs,
                    editMode: 'cell',
                    dataKey: 'id',
                    responsiveLayout: 'scroll',
                    class: 'p-datatable-sm',
                }, {
                    header: () => h('tr', [
                        h('th', 'Label'),
                        h('th', 'Field Name'),
                        h('th', { style: { textAlign: 'center' } }, 'Required'),
                        h('th', { style: { textAlign: 'center' } }, 'Actions'),
                    ]),
                    body: ({ data }) => h('tr', [
                        h('td', [
                            h(InputText, {
                                modelValue: data.label,
                                'onUpdate:modelValue': value => (data.label = value),
                                style: { width: '100%' }
                            })
                        ]),
                        h('td', [
                            h(InputText, {
                                modelValue: data.name,
                                'onUpdate:modelValue': value => (data.name = value),
                                style: { width: '100%' }
                            })
                        ]),
                        h('td', { style: { textAlign: 'center' } }, [
                            h(Checkbox, {
                                modelValue: data.required,
                                'onUpdate:modelValue': value => (data.required = value)
                            })
                        ]),
                        h('td', { style: { textAlign: 'center' } }, [
                            h(Button, {
                                icon: 'pi pi-trash',
                                class: 'p-button-danger',
                                onClick: () => this.removeInput(data.id)
                            })
                        ]),
                    ]),
                }),
                h(Button, {
                    label: 'Add New Input',
                    icon: 'pi pi-plus',
                    class: 'p-button-success mt-3',
                    onClick: this.addInput
                })
            ]),

            h('section', [
                h('h3', 'Options'),
                h('div', { class: 'p-field-checkbox' }, [
                    h(Checkbox, {
                        inputId: 'disableRightClick',
                        modelValue: this.disableRightClick,
                        'onUpdate:modelValue': value => (this.disableRightClick = value)
                    }),
                    h('label', { for: 'disableRightClick', class: 'ml-2' }, 'تعطيل النقر بزر الفأرة الأيمن على الفورم')
                ])
            ])
        ]);
    }
};

createApp(App).use(PrimeVue).component('Button', Button).component('InputText', InputText).component('Checkbox', Checkbox).component('DataTable', DataTable).component('Column', Column).mount('#app');
