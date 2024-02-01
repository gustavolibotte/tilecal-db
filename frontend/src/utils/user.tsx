import { useQuery } from "@tanstack/react-query";
import { makeSnakeCase } from "./string";

interface UserConstructParams {
  username: string;
  roles: Array<string>;
}

export class User{
  username: string;
  roles: Array<string>;

  constructor({ username, roles }: UserConstructParams){
    this.username = username;
    this.roles = roles;
  }

  isRole(role: string): Boolean{
    return this.roles.includes(makeSnakeCase(role));
  }

  isAdmin(): Boolean{
    return this.roles.includes('admin');
  }

  isDeleter(): Boolean{
    return this.roles.includes('deleter');
  }

  isEditor(): Boolean{
    return this.roles.includes('editor');
  }

  isViewer(): Boolean{
    return this.roles.includes('viewer');
  }

  canAll(): Boolean{
    return this.isAdmin();
  }

  canDelete(): Boolean{
    return this.isDeleter();
  }

  canRestore(): Boolean{
    return this.isDeleter();
  }

  canRegister(): Boolean{
    return this.isEditor();
  }

  canAlter(): Boolean{
    return this.isEditor();
  }

  canUpload(): Boolean{
    return this.isEditor();
  }

  canDownload(): Boolean{
    return this.isEditor();
  }

  canView(): Boolean{
    return this.isViewer();
  }
}

export function useUser(): Boolean | User{
    // The backend ensures that the typing is met but I don't know
    // how to make typescript believe in me
  let queryReturn: any = useQuery({
    queryKey: ['user', 'get', {}]
  })

  if (queryReturn.isError){
    console.log('Error retrieving user data');
    console.log(queryReturn.data);
    return false;
  } else if(queryReturn.isPending){
    return false;
  }
  return new User(queryReturn.data.data);
}
