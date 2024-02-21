import './download_button.css';
import { concatenateUrlParams } from '../../utils/routes';

interface DownloadButtonProps{
  downloadUrl: string;
  params?: any;
}

export default function DownloadButton(props: DownloadButtonProps) {
  return (
    <a 
      href={concatenateUrlParams(props.downloadUrl, props.params)} 
      className="p-button font-bold">
      <i className="pi pi-download"/>
    </a>
  )
}
