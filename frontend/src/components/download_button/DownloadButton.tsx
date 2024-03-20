import './download_button.css';
import { concatenateUrlParams } from '../../utils/routes';
import { Button } from 'primereact/button';
import axios from 'axios';
import { toast } from 'react-toastify';
import { midTransactionToastConfig } from '../../utils/toast';

interface DownloadButtonProps{
  downloadUrl: string;
  params?: any;
  filename: string;
}

export default function DownloadButton(props: DownloadButtonProps) {

  // Thanks stackoverflow
  // https://stackoverflow.com/questions/41938718/how-to-download-files-using-axios
  const download =  async () => {
    const toastId = toast.info('Downloading', midTransactionToastConfig);
    await axios({
      url:concatenateUrlParams(props.downloadUrl, props.params),
      method: 'GET',
      responseType: 'blob',
      headers: {
        'Authorization': 'Session'
      }
    }).then((response) => {
      // create file link in browser's memory
      const href = URL.createObjectURL(response.data);

      // create "a" HTML element with href to file & click
      const link = document.createElement('a');
      link.href = href;
      link.setAttribute('download', props.filename); //or any other extension
      document.body.appendChild(link);
      link.click();

      // clean up "a" element & remove ObjectURL
      document.body.removeChild(link);
      URL.revokeObjectURL(href);
      toast.dismiss(toastId)
    }).catch((error) => {
      toast.dismiss(toastId);
      toast.error(__DEFAULT_ERROR_MSG__)
      console.log(error)
    })
  }

  return (
    <Button
      icon="pi pi-download"
      onClick={download}/>
  )
}
