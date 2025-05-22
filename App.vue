<template>
  <div class="p-4">
    <h1>Custom COD Admin Panel</h1>

    <section>
      <h2>Product</h2>
      <!-- Product selection and edit form here -->
    </section>

    <section class="mt-6">
      <h2>Form Inputs</h2>
      <DataTable :value="inputs" editMode="cell" dataKey="id" class="p-datatable-sm" responsiveLayout="scroll">
        <Column field="label" header="Label" editor="true" :editorOptions="{onChange: onCellEdit}"></Column>
        <Column field="name" header="Field Name" editor="true"></Column>
        <Column header="Required" body="requiredTemplate"></Column>
        <Column header="Actions" body="actionTemplate"></Column>
      </DataTable>
      <Button label="Add Input" icon="pi pi-plus" class="p-button-success mt-2" @click="addInput" />
    </section>

    <section class="mt-6">
      <h2>Options</h2>
      <Checkbox v-model="disableRightClick" inputId="disableRightClick" />
      <label for="disableRightClick">Disable right click on form</label>
    </section>
  </div>
</template>

<script>
export default {
  data() {
    return {
      inputs: [
        { id: 1, label: 'الإسم الكامل', name: 'full_name', required: true },
        { id: 2, label: 'رقم الهاتف', name: 'phone', required: true },
        { id: 3, label: 'العنوان', name: 'address', required: true },
        { id: 4, label: 'المدينة', name: 'city', required: true },
      ],
      disableRightClick: false,
    };
  },
  methods: {
    addInput() {
      this.inputs.push({
        id: this.inputs.length + 1,
        label: '',
        name: '',
        required: false,
      });
    },
    requiredTemplate(rowData) {
      return (
        <Checkbox
          modelValue={rowData.required}
          onChange={(e) => {
            rowData.required = e.checked;
          }}
        />
      );
    },
    actionTemplate(rowData) {
      return (
        <Button
          icon="pi pi-trash"
          class="p-button-danger"
          onClick={() => {
            this.inputs = this.inputs.filter((i) => i.id !== rowData.id);
          }}
        />
      );
    },
    onCellEdit(e) {
      // handle cell edits if needed
    },
  },
};
</script>

<style>
/* Optional global styles */
</style>
