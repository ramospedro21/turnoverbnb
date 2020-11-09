<template>
	<div class="container mt-5 pt-5">
		<div class="row justify-content-center">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<div class="row align-items-center">
							<div class="col-md-6">
								<h3 class="card-title h5">Products</h3>
							</div>
                            <div class="col-md-3 text-right">
                                <button type="button" class="btn btn-info" @click="editBulkProducts(0)" v-if="selectedProducts.length == 0">Edit all products</button>
                                <button type="button" class="btn btn-info" @click="editBulkProducts(1)" v-else>Edit selected products</button>
                            </div>
							<div class="col-md-3 text-right text-secondary">
                                <button type="button" class="btn btn-block btn-info" @click="create()">
									<i class="fa fa-plus mr-2"></i> New
                                </button>
							</div>
						</div>
					</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>
                                        Price
                                        <button v-on:click="orderBy('price')" class="btn btn-link btn-sm px-0 py-0">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </th>
                                    <th>Quantity</th>
                                    <th>
                                    </th>
                                </tr>
                                <tr v-for="product in products.data" :key="product.id">
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" :id="'select-product-' + product.id" v-model="selectedProducts" type="checkbox" :value="product">
                                            <label class="custom-control-label" :for="'select-product-' + product.id"></label>
                                        </div>
                                    </td>
                                    <td>{{ product.name }}</td>
                                    <td>{{ product.sku }}</td>
                                    <td>{{ product.price | money }}</td>
                                    <td>{{ product.quantity }}</td>
                                    <td>
                                        <i class="pointer fas fa-history mr-1" @click="openHistoryModal(product)"></i>
                                        <i class="pointer fas fa-edit mr-1" @click="edit(product)"></i>
                                        <i class="pointer fas fa-trash" @click="openDeleteModal(product)"></i>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-12 ">
                                <ul class="pagination float-right mr-4">
                                    <li class="page-item" v-if="products.current_page > 1"
                                        @click="index(products.current_page - 1)">
                                        <a class="page-link pointer">
                                            <i class="fas fa-angle-left"/>
                                        </a>
                                    </li>
                                    <li class="page-item pointer" v-for="p in products.last_page"
                                        :key="p" :class="(p === products.current_page) ? 'active' : '' "
                                        @click="index(p)">
                                        <a class="page-link">
                                            {{ p }}
                                        </a>
                                    </li>
                                    <li class="page-item pointer" v-if="products.current_page < products.last_page"
                                        @click="index(products.current_page + 1)">
                                        <a class="page-link">
                                            <i class="fas fa-angle-right"/>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
				</div>
			</div>
		</div>
        
        <!-- CREATE AND UPDATE MODAL -->
        <div class="modal fade" id="productsModal" tabindex="-1" role="dialog" aria-labelledby="productLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productLabel">{{ product.id ? 'Edit product' : 'Add new product' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="product.id ? update() : store()">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="form-control-label">Name: *</label>
                                        <input type="text" class="form-control" v-model="product.name" required>
                                    </div>
                                </div>
                                <div class="col" v-if="product.id">
                                    <div class="form-group">
                                        <label class="form-control-label">Slug: *</label>
                                        <input type="text" class="form-control" v-model="product.slug" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="form-control-label">Price: *</label>
                                        <money v-bind="money" class="form-control" v-model="product.price"/>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label class="form-control-label">Quantity: *</label>
                                        <input type="number" class="form-control" v-model="product.quantity" required>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label class="form-control-label">SKU: *</label>
                                        <input type="number" class="form-control" v-model="product.sku" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-white">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="btnAddBox" class="btn btn-success" :disabled="loading.buttonProduct == true">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- HISTORY MODAL -->
        <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="historyLabel">Product {{ product.name }} history</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <tr>
                                <th>Desc</th>
                                <th>Author</th>
                                <td>Created at</td>
                            </tr>
                            <tr v-for="log in product.logs" :key="log.id">
                                <td>{{ log.desc }}</td>
                                <td>{{ log.user.name }}</td>
                                <td>{{ log.created_at | datetime }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- BULK EDIT MODAL -->
        <div class="modal fade" id="editBulkModal" tabindex="-1" role="dialog" aria-labelledby="editBulkLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBulkLabel">Bulk edit products</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editBulk()">
                        <div class="modal-body">
                            <div class="row">
                                <table class="table">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>SKU</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                    </tr>
                                    <tr  v-for="p in selectedProducts" :key="p.id">
                                        <th scope="row">
                                            <input type="text" class="form-control" v-model="p.id" disabled>
                                        </th>
                                        <td>
                                            <input type="text" class="form-control" v-model="p.name">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" v-model="p.slug">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" v-model="p.sku">
                                        </td>
                                        <td>
                                            <money v-bind="money" class="form-control" v-model="p.price"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" v-model="p.quantity">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-white">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="btnAddBox" class="btn btn-success" :disabled="loading.buttonBulk == true">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-labelledby="deleteProductLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteProductLabel">Delete product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="destroy()">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-primary h4">Are you sure you want delete the product: "{{ product.name }}" ?</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" :disabled="loading.buttonDeleteProduct == true">Yes, delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	</div>
</template>

<script>

import {showSuccessToast, showErrorToast, showInfoToast} from '../../../helpers/animations';
import { Money } from 'v-money';

export default {

    data(){
        return{
            products: {
                total: 0,
                current_page: 1,
                data: [],
				last_page: 1,
				per_page: 16,
				from: 0,
				to: 0
            },

            product: {
                name: null,
                slug: null,
                price: null,
                quantity: null,
                sku: null,
            },

            loading: {
                buttonProduct: false,
                buttonBulk: false,
                page: false,
            },
            
            money: {
                decimal: '.',
                thousands: '.',
                prefix: '$ ',
                suffix: '',
                precision: 2,
                masked: false /* doesn't work with directive */
            },

            filters: {
                per_page: 16
            },

            selectedProducts: [],
            by: '',
            order: 'asc',
        }
    },

    methods: {
        index: async function(page){
            try{

                let url = '/painel/product?';

                url +=  '&page=' + ((page) ? page : this.products.current_page);
                if (this.filters.per_page !== 16) url += '&per_page=' + this.filters.per_page;
                if (this.by !== '') url += '&by=' + this.by;
                if (this.order !== '') url += '&order=' + this.order;

                const {data} = await axios.get(url);
                
                this.loading.page = false

                this.products = data;

            }catch(e){
                
                showErrorToast('It wasn’t possible show the products.');
            }
        },

        create: function(){

            this.product = {
                name: null,
                slug: null,
                price: null,
                quantity: null,
                sku: null,
            }

            $("#productsModal").modal('show');

        },

        store: async function(){

            this.loading.buttonProduct = true;
            
            try{

                const {data} = await axios.post(`/painel/product`, {
                    product: this.product,
                });

                showSuccessToast('The product was successfully registered.');

                this.loading.buttonProduct = false;

                this.index(),

                $("#productsModal").modal('hide');

            }catch(e){  
                this.loading.buttonProduct = false;

                showErrorToast('The product wasn’t successfully registered.');
            }
        },

        edit: function(product){

            this.product = {
                ...product
            }
            
            $("#productsModal").modal('show');
        },

        update: async function(){
            
            this.loading.buttonProduct = true;

            try{

                const {data} = await axios.patch(`/painel/product/${this.product.id}`, {
                    product: this.product
                });

                this.index();

                this.loading.buttonProduct = false;

                showSuccessToast('The product was successfully edited.');
                
                $("#productsModal").modal('hide');

            }catch(e){

                this.loading.buttonProduct = false;

                showErrorToast('The product wasn’t successfully edited.');
            }
        },

        openDeleteModal: async function(product){
            this.product = {
                ...product
            }

            $("#deleteProductModal").modal('show');
        },

        destroy: async function(){
            try{

                await axios.delete(`/painel/product/${this.product.id}`)
                
                this.index();

                this.loading.buttonProduct = false;

                showSuccessToast('The product was successfully deleted.');
                
                $("#deleteProductModal").modal('hide');

            }catch(e){

                this.loading.buttonProduct = false;

                showErrorToast('The product wasn’t successfully deleted.');

            }
        },

        openHistoryModal: function(product){

            this.product = {
                ...product
            }

            $("#historyModal").modal('show');
        },

        editBulkProducts: function(status){

            if(status == 0) this.selectedProducts = this.products.data;

            $("#editBulkModal").modal('show');

        },

        editBulk: async function(){

            this.loading.buttonBulk = true;

            try{

                const {data} = await axios.post(`/painel/product/bulk`,{
                    products: this.selectedProducts,
                });

                this.loading.buttonBulk = false;

                this.index();

                showSuccessToast('Bulk products was successfully edited.');

                $("#editBulkModal").modal('hide');

            }catch(e){

                this.loading.buttonBulk = false;

                showErrorToast('Bulk products wasn’t successfully edited.');

            }
        },

        orderBy: function(string){

            if(string == this.by && this.order == 'desc') this.order = 'asc';
            else if(string == this.by && this.order == 'asc') this.order = 'desc';
            else this.order == 'asc';
            this.by = string;
            this.index(1);
        },
    },
    
    mounted(){
        this.index();
    }

}
</script>

<style>
.pointer{
    cursor: pointer;
}
</style>