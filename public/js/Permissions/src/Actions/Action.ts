import {Fetch} from '../../../System/Components/Fetch/Fetch.js';

export class Action {
  public static async addEmployeeToPermissionGroup(
    permission_id: number,
    employee_id: number,
  ) {
    return Fetch.store('/user/permission/add', {
      permission_id: permission_id,
      employee_id: employee_id,
    });
  }

  public static async removeEmployeeFromPermissionGroup(
    permission_id: number,
    employee_id: number,
  ) {
    return Fetch.update('/user/permission/remove', {
      permission_id: permission_id,
      employee_id: employee_id,
    });
  }
}
