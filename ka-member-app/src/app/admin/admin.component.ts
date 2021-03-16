import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { User, Address } from '@app/_models';
import { UserService } from '@app/_services';

@Component({ templateUrl: 'admin.component.html' })
export class AdminComponent implements OnInit {
    loading = false;
    users: User[] = [];
    productName: string;
    selectedUser!: User;
    address!: Address;
  
    onSubmit(value: string): void {
      console.log('you submitted value: ', value);
    }

    constructor(private userService: UserService) { 
        this.productName = "ng-book: The Complete Guide to Angular"        
    }

    ngOnInit() {
        this.loading = true;
        this.userService.getAll().pipe(first()).subscribe(users => {
            this.loading = false;
            this.users = users;
        });
    }

    onProductChange(value: string) {
        // do something else with the value
        console.log(value);
    
        // remember to update the selectedValue
        this.productName = value;
      }

      onUserChange(value: any) {
        // do something else with the value
        console.log(value);
    
        // remember to update the selectedValue
        this.selectedUser = value;

        console.log(this.selectedUser);
      }

      onUpddatedAddress(value: any) {
        console.log(value);        
        this.address = value;
      }
}